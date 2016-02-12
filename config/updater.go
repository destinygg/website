package main

import (
	"bufio"
	"bytes"
	"encoding/base64"
	"encoding/json"
	"flag"
	"io"
	"io/ioutil"
	"log"
	"net"
	"net/http"
	"net/url"
	"os"
	"os/exec"
	"strings"
	"sync"
	"time"
)

var (
	sockPath   = flag.String("sock", "/run/haproxy/admin.sock", "the haproxy socket to write to")
	ipbanPath  = flag.String("ipban", "/var/lib/haproxy/ipban.map", "the haproxy ipban map")
	issuerPath = flag.String("issuer", "/etc/ssl/certs/COMODORSADomainValidationSecureServerCA.crt", "the certificate issuer's certificate path")
	certPath   = flag.String("cert", "/etc/ssl/certs/destiny.gg.crt", "the certificate path")
	ocspURL    = flag.String("ocspurl", "http://ocsp.comodoca.com", "the OCSP URL")
)

func init() {
	flag.Parse()
	log.SetFlags(log.LstdFlags | log.LUTC | log.Lshortfile)
}

func handleErr(err error) {
	if err != nil {
		log.Output(2, err.Error())
		os.Exit(1)
	}
}

func getIPs() (ret []string) {
	urls := []string{
		"http://ipv4.myexternalip.com/json",
		"http://ipv6.myexternalip.com/json",
	}

	var ip = &struct {
		IP string `json:"ip"`
	}{}

	for _, url := range urls {
		res, err := http.Get(url)
		if err != nil {
			log.Fatal(err)
		}

		dec := json.NewDecoder(res.Body)
		err = dec.Decode(&ip)
		if err != nil {
			log.Fatal(err)
		}

		res.Body.Close()
		ret = append(ret, ip.IP)
	}

	return
}

func getTorNodes(ips, ports []string) []string {
	const torURL = "https://check.torproject.org/cgi-bin/TorBulkExitList.py"
	urls := []string{}
	for _, ip := range ips {
		for _, port := range ports {
			u, err := url.Parse(torURL)
			if err != nil {
				log.Fatal(err)
			}
			q := u.Query()
			q.Add("ip", ip)
			q.Add("port", port)
			u.RawQuery = q.Encode()
			urls = append(urls, u.String())
		}
	}

	m := map[string]struct{}{}
	for _, u := range urls {
		res, err := http.Get(u)
		handleErr(err)

		sc := bufio.NewScanner(res.Body)
		for sc.Scan() {
			line := sc.Text()
			if line[0] == '#' {
				continue
			}

			// make sure the line is a valid IP
			if net.ParseIP(line) == nil {
				log.Printf("Invalid Tor node address: %v\n", line)
				continue
			}

			m[line] = struct{}{}
		}

		res.Body.Close()
		err = sc.Err()
		handleErr(err)
	}

	log.Printf("Banning %v Tor nodes\n", len(m))
	ret := make([]string, 0, len(m))
	for ip := range m {
		ret = append(ret, ip)
	}

	return ret
}

type haproxy struct {
	sock    net.Conn
	done    chan struct{}
	wg      sync.WaitGroup
	scratch []byte
	buf     bytes.Buffer
}

func (h *haproxy) Init(path string) {
	h.done = make(chan struct{})
	c, err := net.Dial("unix", path)
	handleErr(err)

	h.sock = c
	h.scratch = make([]byte, 512)
	h.wg.Add(1)
	go h.read()
}

func (h *haproxy) Close() {
	if c, ok := h.sock.(*net.UnixConn); ok {
		c.CloseWrite()
	}

	close(h.done)
	h.wg.Wait()
	h.sock.Close()
}

func (h *haproxy) read() {
	var done bool
	defer h.wg.Done()

	for {
		h.sock.SetReadDeadline(time.Now().Add(time.Second))
		n, err := h.sock.Read(h.scratch)
		if operr, ok := err.(*net.OpError); ok {
			if operr.Temporary() {
				log.Printf("Read err: %v\n", err.Error())
				continue
			}

			if !operr.Temporary() && !operr.Timeout() {
				// not timeout and not temporary ergo fatal
				handleErr(err)
			}
		}

		if n == 0 && done {
			break
		}

		// there will be a newline indicating success for every
		// pipelined command if no error found
		// we do not care about success
		s := strings.TrimSpace(string(h.scratch[:n]))
		if len(s) > 0 {
			log.Printf("Response from haproxy: %q\n", s)
		}

		select {
		case <-h.done: // channel got closed, we are quitting
			done = true
		default:
		}
	}
}

func (h *haproxy) Write() {
	_, err := h.sock.Write(h.buf.Bytes())
	handleErr(err)
}

func (h *haproxy) UpdateIPMap(path string) {
	f, err := os.OpenFile(path, os.O_CREATE|os.O_TRUNC|os.O_RDWR, 0660)
	handleErr(err)
	defer f.Close()

	// first clear the banned IPs, this leaves a small window where
	// users could connect
	h.buf.Reset()
	h.buf.WriteString(`clear map `)
	h.buf.WriteString(path)
	h.buf.WriteString(";")
	h.Write()

	ips := getIPs()
	ports := []string{"80", "443"}

	nodes := getTorNodes(ips, ports)
	for _, node := range nodes {
		_, err = f.WriteString(node + " 1 \n")
		handleErr(err)

		h.buf.Reset()
		h.buf.WriteString("add map ")
		h.buf.WriteString(path)
		h.buf.WriteString(" ")
		h.buf.WriteString(node)
		h.buf.WriteString(" 1;")
		h.Write()
	}
}

func (h *haproxy) UpdateOCSP(issuer, cert, url string) {
	f, err := ioutil.TempFile("", "ocsp-")
	handleErr(err)

	fname := f.Name()
	defer os.Remove(fname)
	defer f.Close()

	cmd := exec.Command(
		"openssl", "ocsp", "-noverify",
		"-issuer", issuer,
		"-cert", cert,
		"-url", url,
		"-respout", fname,
	)
	err = cmd.Run()
	handleErr(err)

	// read tempfile into buffer
	d := &bytes.Buffer{}
	io.Copy(d, f)

	h.buf.Reset()
	h.buf.WriteString("set ssl ocsp-response ")

	// encode the ocsp into base64 and write it to the buffer
	encoder := base64.NewEncoder(base64.StdEncoding, &h.buf)
	encoder.Write(d.Bytes())
	encoder.Close()

	h.buf.WriteString(";")
	h.Write()
}

func main() {
	// any error is fatal
	h := &haproxy{}
	h.Init(*sockPath)
	log.Println("Opened haproxy socket")
	h.UpdateIPMap(*ipbanPath)
	log.Println("Tor node IP bans updated")
	h.UpdateOCSP(*issuerPath, *certPath, *ocspURL)
	log.Println("OCSP refreshed")
	h.Close()
	os.Exit(0) // success
}
