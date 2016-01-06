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

func (c *Client) call(path string, params url.Values) (map[string]interface{}) {
	return c.request("POST", path, params)
}

func (c *Client) get(path string, params url.Values) (map[string]interface{}) {
	return c.request("GET", path, params)
}

func (c *Client) delete(path string) (map[string]interface{}) {
	return c.request("DELETE", path, url.Values{});
}

func (c *Client) deleteData(path string) (map[string]interface{}) {
	var res = c.delete(path)
	return c.resultFromRes(res).(map[string]interface{})
}

func (c *Client) deleteArrayData(path string) ([]interface{}) {
	var res = c.delete(path)
	return c.resultFromRes(res).([]interface{})
}

func (c *Client) getData(path string, params url.Values) (map[string]interface{}) {
	var res = c.get(path, params)
	return c.resultFromRes(res).(map[string]interface{})
}

func (c *Client) getArrayData(path string, params url.Values) ([]interface{}) {
	var res = c.get(path, params)
	return c.resultFromRes(res).([]interface{})
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

func (c *Client) request(method string, path string, params url.Values) (map[string]interface{}) {
	urlStr := baseUrl(path)
	paramStr := bytes.NewBufferString(params.Encode())

	var req *http.Request;
	var err error;
	if (method == "GET") {
		req, err = http.NewRequest(method, fmt.Sprintf("%s?%s", urlStr, paramStr), nil)
	} else if (method == "POST") {
		req, err = http.NewRequest(method, urlStr, paramStr)
		req.Header.Set("Content-Type", "application/x-www-form-urlencoded")
	} else {
		req, err = http.NewRequest(method, urlStr, paramStr);
	}
	checkErr(err)
	if len(c.sessionToken) > 0 {
		req.Header.Set("X-CR-Session", c.sessionToken)
	}

	fmt.Println("curl -X", method, urlStr, params)

	body, doErr := c.do(req)
	checkErr(doErr)
	defer body.Close()

	buf := new(bytes.Buffer)
	buf.ReadFrom(body)
	bodyStr := buf.String()
	ioutil.WriteFile("error.html", []byte(bodyStr), 0644);
	fmt.Println("response:", bodyStr)

	fmt.Println()

	var dat map[string]interface{}

	jsonErr := json.Unmarshal([]byte(bodyStr), &dat)
	checkErr(jsonErr)

	return dat
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

func (c *Client)resultFromRes(res map[string]interface{}) interface{} {
	if (toInt(res["code"]) != 0) {
		panic("code is not 0")
	}
	var data interface{}
	if (res["result"] != nil) {
		data = res["result"].(interface{})
	}

	if mapData, isMap := data.(map[string]interface{}); isMap {
		if sessionToken, ok := mapData["sessionToken"].(string); ok {
			c.sessionToken = sessionToken
		}
	}

	return data
}

func (c *Client) callData(path string, params url.Values) (map[string]interface{}) {
	res := c.call(path, params)
	return c.resultFromRes(res).(map[string]interface{})
}

func (c *Client) callArrayData(path string, params url.Values) ([]interface{}) {
	res := c.call(path, params)
	return c.resultFromRes(res).([]interface{})
}

// perform the request.
func (c *Client) do(req *http.Request) (io.ReadCloser, error) {
	res, err := c.HTTPClient.Do(req)
	checkErr(err)

	if res.StatusCode < 400 {
		return res.Body, err
	}

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
		res.Body.Close()
	}

	if (strings.Contains(kind, "text/html")) {

		fmt.Println("PHP Error, Please see error.html");
		return res.Body, nil
	}

	if err := json.NewDecoder(res.Body).Decode(e); err != nil {
		return nil, err
	}

	res.Body.Close()

	return nil, e
}

func readString(reader io.ReadCloser) (string) {
	buf := new(bytes.Buffer)
	buf.ReadFrom(reader)
	s := buf.String()
	return s
}
