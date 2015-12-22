package codereview

import (
	"fmt"
	"log"
	"encoding/json"
	"io"
	"bytes"
	"io/ioutil"
	"net/http"
	"net/url"
	"strings"
	"reflect"
	"os"
)

type Client struct {
	HTTPClient   *http.Client
	sessionToken string
}

func NewClient() *Client {
	return &Client{
		HTTPClient: http.DefaultClient,
	}
}

func unused() {
	fmt.Printf("")
	log.Fatal()
	reflect.TypeOf("string")
}

func (c *Client) call(path string, params url.Values) (map[string]interface{}, error) {
	return c.request("POST", path, params)
}

func (c *Client) get(path string, params url.Values) (map[string]interface{}, error) {
	return c.request("GET", path, params)
}

func (c *Client) getData(path string, params url.Values) (map[string]interface{}) {
	var res, err = c.get(path, params)
	return c.resultDataFromRes(res, err)
}

func baseUrl(path string) (string) {
	var urlStr string
	urlStr = "http://localhost:3005/" + path
//
//	prod := os.Getenv("PROD")
//
//	if prod != "" {
//		urlStr = "http://reviewcode.cn/" + path
//	} else {
//
//	}

	return urlStr
}

func (c *Client) request(method string, path string, params url.Values) (map[string]interface{}, error) {
	urlStr := baseUrl(path)
	paramStr := bytes.NewBufferString(params.Encode())

	req, err := http.NewRequest(method, urlStr, paramStr)

	if (method == "GET") {
		req, err = http.NewRequest(method, fmt.Sprintf("%s?%s", urlStr, paramStr), nil)
	}
	if err != nil {
		return nil, err
	}
	if (method == "POST") {
		req.Header.Set("Content-Type", "application/x-www-form-urlencoded")
	}
	if len(c.sessionToken) > 0 {
		req.Header.Set("X-CR-Session", c.sessionToken)
	}

	fmt.Println("curl", urlStr, params)

	body, doErr := c.do(req)
	checkErr(doErr)
	defer body.Close()

	var dat map[string]interface{}

	decoder := json.NewDecoder(body)
	jsonErr := decoder.Decode(&dat);
	checkErr(jsonErr)

	fmt.Println("response:", dat)
	fmt.Println()

	return dat, nil
}

func (c *Client) callWithStr(path string, body string) map[string]interface{} {
	urlStr := baseUrl(path)
	req, err := http.NewRequest("POST", urlStr, strings.NewReader(body))
	checkErr(err)
	req.Header.Set("Content-Type", "plain/text")
	fmt.Println("curl", urlStr, body)

	doBody, doErr := c.do(req)
	checkErr(doErr)
	defer doBody.Close()

	var dat map[string]interface{}

	decoder := json.NewDecoder(doBody)
	jsonErr := decoder.Decode(&dat);
	checkErr(jsonErr)

	fmt.Println("response:", dat)
	fmt.Println()

	return dat
}

func (c *Client)resultDataFromRes(res map[string]interface{}, error interface{}) map[string]interface{} {
	if error != nil {
		panic(error)
	}
	if (toInt(res["resultCode"]) != 0) {
		panic("resultCode is not 0")
	}
	var data map[string]interface{}
	if (res["resultData"] != nil) {
		data = res["resultData"].(map[string]interface{})
	}

	if sessionToken, ok := data["sessionToken"].(string); ok {
		c.sessionToken = sessionToken
	}

	return data
}

func (c *Client) callData(path string, params url.Values) (map[string]interface{}) {
	res, err := c.call(path, params)
	return c.resultDataFromRes(res, err)
}

// perform the request.
func (c *Client) do(req *http.Request) (io.ReadCloser, error) {
	res, err := c.HTTPClient.Do(req)
	checkErr(err)

	if res.StatusCode < 400 {
		return res.Body, err
	}

	defer res.Body.Close()

	e := &Error{
		Status:     http.StatusText(res.StatusCode),
		StatusCode: res.StatusCode,
	}

	kind := res.Header.Get("Content-Type")

	if strings.Contains(kind, "text/plain") {
		if b, err := ioutil.ReadAll(res.Body); err == nil {
			e.Summary = string(b)
			return nil, e
		} else {
			return nil, err
		}
	}

	if (strings.Contains(kind, "text/html")) {
		if b, err := ioutil.ReadAll(res.Body); err == nil {
			ioutil.WriteFile("error.html", b, 0644);
			panic("PHP Error, Please see error.html");
		}
	}

	if err := json.NewDecoder(res.Body).Decode(e); err != nil {
		return nil, err
	}

	return nil, e
}

func readString(reader io.ReadCloser) (string) {
	buf := new(bytes.Buffer)
	buf.ReadFrom(reader)
	s := buf.String()
	return s
}
