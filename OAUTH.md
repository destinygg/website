# Destiny.gg Authentication
OAuth style authentication integration.

**THIS GUIDE IS INCOMPLETE**

### First thing to do
* Register an application in your [destiny.gg](https://www.destiny.gg/profile/developer) profile if you haven't.
* Copy the ID (`client_id`) and Secret from your application

##### TLDR
* Send user to authorize url `/oauth/authorize`, the user logs in...
* Get response `code` from the URL then do a token exchange `/oauth/token` api call for an `access_token`
* Use the access token to get the user info `/api/userinfo?token=x8yf[...]f0c`

## Authorize

#### Request
```
GET https://www.destiny.gg/oauth/authorize
```

<table>
    <thead>
      <tr>
        <td>Parameter</td>
        <td>Value</td>
      </tr>
    <thead>
    <tbody>
      <tr>
        <td>response_type</td>
        <td>must be "code" - indicates that you expect to receive an authorization code</td>
      </tr>
      <tr>
        <td>client_id</td>
        <td>The client ID you received when you first created the application</td>
      </tr>
      <tr>
        <td>redirect_uri</td>
        <td>Indicates the URL to return to after authorization is complete, such as org.example.app://redirect</td>
      </tr>
      <tr>
        <td>state</td>
        <td>Arbitrary alphanumeric string that you'll send and then verify, max 64 characters long.</td>
      </tr>
      <tr>
        <td>code_challenge</td>
        <td>The code challenge generated as described below</td>
      </tr>
    <tbody>
</table>

##### Code Challenge
```js
let secret = hash("sha256", CLIENT_SECRET)
let code_verifier = 'Fwef[...]8ehyf9' //  Random URL-safe string with a minimum length of 43 characters.
let code_challenge = base64_encode(hash("sha256", code_verifier + secret))
```

#### Response
Destiny.gg will issue a 302 redirect to the url specified in the `redirect_uri` with the `state` and `code` parameters.

```
Location: org.example.app://redirect?state=...&code=...
```
You should check that the `state` against the initial value.

The `code` can then be used to do a token exchange.

## Token Exchange
Exchange your authorization `code` for an `access_token`

#### Request
```
GET https://www.destiny.gg/oauth/token
```

<table>
    <thead>
      <tr>
        <td>Parameter</td>
        <td>Value</td>
      </tr>
    <thead>
    <tbody>
      <tr>
        <td>grant_type</td>
        <td>Must be "authorization_code"</td>
      </tr>
      <tr>
        <td>code</td>
        <td>The client will send the authorization code it obtained in the redirect</td>
      </tr>
      <tr>
        <td>client_id</td>
        <td>The application’s registered client ID</td>
      </tr>
      <tr>
        <td>redirect_uri</td>
        <td>The redirect URL that was used in the initial authorization request</td>
      </tr>
      <tr>
        <td>code_verifier</td>
        <td>The code verifier portion used in the initial /oauth/authorize endpoint (see above would be 'Fwef[...]8ehyf9')</td>
      </tr>
    <tbody>
</table>

#### Response
```json
{
    "access_token" : "VdD03YOa2GYbjfnpZm0hhzb7OeyvO5Fp5lWOQbFlYGKQ4MVN1iEZcmwJh5VBFhYf",
    "refresh_token" : "kWGB9cxqxUJXsHDA2S0rbOaqStaxEmPu1R0Eu9kqkchMXnu34shGYYcH3iDIqE7R",
    "expires_in" : 3600,
    "scope" : "identify",
    "token_type" : "bearer"
}
```


## Renew Token
When an `access_token` expires, you will receive the following error 
```json
{
    "error" : "token_expired",
    "message" : "The token has expired.",
    "code" : 403
}
```

#### Request

```
GET https://www.destiny.gg/oauth/token
```
<table>
    <thead>
      <tr>
        <td>Parameter</td>
        <td>Value</td>
      </tr>
    <thead>
    <tbody>
      <tr>
        <td>grant_type</td>
        <td>Must be "refresh_token"</td>
      </tr>
      <tr>
        <td>client_id</td>
        <td>The application’s registered client ID</td>
      </tr>
      <tr>
        <td>refresh_token</td>
        <td>The refresh token</td>
      </tr>
    <tbody>
</table>

The response is the same as the token exchange response.

