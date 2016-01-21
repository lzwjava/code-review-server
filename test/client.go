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
	"net/http/cookiejar"
)

type Client struct {
	HTTPClient   *http.Client
	sessionToken string
	cookieJar    *cookiejar.Jar
	admin        bool
}

func NewClient() *Client {
	cookieJar, _ := cookiejar.New(nil)
	return &Client{
		HTTPClient: &http.Client{Jar:cookieJar},
		cookieJar: cookieJar,
		admin: false,
	}
}

func unused() {
	fmt.Printf("")
	log.Fatal()
	reflect.TypeOf("string")
}

func (c *Client) post(path string, params url.Values) (map[string]interface{}) {
	return c.request("POST", path, params)
}

func (c *Client) get(path string, params url.Values) (map[string]interface{}) {
	return c.request("GET", path, params)
}

func (c *Client) delete(path string) (map[string]interface{}) {
	return c.request("DELETE", path, url.Values{});
}

func (c *Client) patch(path string, params url.Values) (map[string]interface{}) {
	return c.request("PATCH", path, params);
}

func (c *Client) patchData(path string, params url.Values) (map[string]interface{}) {
	res := c.patch(path, params)
	return c.resultFromRes(res).(map[string]interface{})
}

func (c *Client) patchArrayData(path string, params url.Values) ([]interface{}) {
	res := c.patch(path, params)
	return c.resultFromRes(res).([]interface{})
}

func (c *Client) postData(path string, params url.Values) (map[string]interface{}) {
	res := c.post(path, params)
	return c.resultFromRes(res).(map[string]interface{})
}

func (c *Client) postArrayData(path string, params url.Values) ([]interface{}) {
	res := c.post(path, params)
	return c.resultFromRes(res).([]interface{})
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

func (c *Client) getListData(path string, params url.Values) ([]interface{}, int) {
	res := c.get(path, params)
	return c.resultFromRes(res).([]interface{}), toInt(res["total"])
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
	} else if (method == "POST" || method == "PATCH") {
		req, err = http.NewRequest(method, urlStr, paramStr)
		req.Header.Set("Content-Type", "application/x-www-form-urlencoded")
	} else {
		req, err = http.NewRequest(method, urlStr, paramStr);
	}
	checkErr(err)
	if len(c.sessionToken) > 0 {
		req.Header.Set("X-CR-Session", c.sessionToken)
	}
	if (c.admin) {
		req.SetBasicAuth("admin", "Pwx9uVJM");
	}

	fmt.Println("curl -X", method, urlStr, params)

	body, doErr := c.do(req)
	checkErr(doErr)
	defer body.Close()

	bodyStr := writeHtml(body)

	fmt.Println("response:", bodyStr)
	fmt.Println()

	var dat map[string]interface{}

	jsonErr := json.Unmarshal([]byte(bodyStr), &dat)
	checkErr(jsonErr)

	return dat
}

func writeHtml(body io.ReadCloser) string {
	buf := new(bytes.Buffer)
	buf.ReadFrom(body)
	bodyStr := buf.String()
	ioutil.WriteFile("error.html", []byte(bodyStr), 0644);
	return bodyStr;
}

func (c *Client) postWithStr(path string, body string) map[string]interface{} {
	urlStr := baseUrl(path)
	req, err := http.NewRequest("POST", urlStr, strings.NewReader(body))
	checkErr(err)
	req.Header.Set("Content-Type", "plain/text")
	fmt.Println("curl", urlStr, body)

	doBody, doErr := c.do(req)
	checkErr(doErr)
	defer doBody.Close()

	bodyStr := writeHtml(doBody)

	var dat map[string]interface{}

	jsonErr := json.Unmarshal([]byte(bodyStr), &dat);
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

// perform the request.
func (c *Client) do(req *http.Request) (io.ReadCloser, error) {
	res, err := c.HTTPClient.Do(req)
	checkErr(err)

	//	cookie, cookieErr := req.Cookie("crtoken")
	//	checkErr(cookieErr)

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
