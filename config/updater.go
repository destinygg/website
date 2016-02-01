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
		if err != nil {
			log.Fatal(err)
		}

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
		if err := sc.Err(); err != nil {
			log.Fatal(err)
		}
	}

	log.Printf("Banning %v Tor nodes\n", len(m))
	ret := make([]string, 0, len(m))
	for ip := range m {
		ret = append(ret, ip)
	}

	return ret
}

type haproxy struct {
	path    string
	scratch []byte
}

func (h *haproxy) Init(path string) {
	c, err := net.Dial("unix", path)
	handleErr(err)
	h.path = path
	h.scratch = make([]byte, 512)
	c.Close()
}

func (h *haproxy) Write(b []byte) (int, error) {
	// cant connect to the unix socket too quickly or it will error out with
	// connect: resource temporarily unavailable
	// thus the read with the deadline
	c, err := net.Dial("unix", h.path)
	handleErr(err)
	defer c.Close()

	ret, err := c.Write(b)
	handleErr(err)

	c.SetReadDeadline(time.Now().Add(10 * time.Millisecond))
	n, _ := c.Read(h.scratch)
	if n > 0 {
		log.Println(string(h.scratch[:n]))
	}

	return ret, err
}

func (h *haproxy) UpdateIPMap(path string) {
	f, err := os.OpenFile(path, os.O_CREATE|os.O_TRUNC|os.O_RDWR, 0660)
	handleErr(err)
	defer f.Close()

	// first clear the banned IPs, this leaves a small window where
	// users could connect
	b := bytes.NewBufferString(`clear map `)
	b.WriteString(path)
	_, err = h.Write(b.Bytes())
	handleErr(err)

	ips := getIPs()
	ports := []string{"80", "443"}

	nodes := getTorNodes(ips, ports)
	for _, node := range nodes {
		f.WriteString(node + " 1 \n")

		b.Reset()
		b.WriteString("add map ")
		b.WriteString(path)
		b.WriteString(" ")
		b.WriteString(node)
		b.WriteString(" 1")
		_, err = h.Write(b.Bytes())
		handleErr(err)
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

	b := bytes.NewBufferString("set ssl ocsp-response ")

	// encode the ocsp into base64 and write it to the buffer
	encoder := base64.NewEncoder(base64.StdEncoding, b)
	encoder.Write(d.Bytes())
	encoder.Close()

	_, err = h.Write(b.Bytes())
	handleErr(err)
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
	os.Exit(0) // success
}
