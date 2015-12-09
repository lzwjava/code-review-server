package codereview

import (
	"testing"
	"github.com/stretchr/testify/assert"
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
	HTTPClient  *http.Client
}

func unused() {
	fmt.Printf("")
	log.Fatal()
	reflect.TypeOf("string")
}

func NewClient() *Client {
	return &Client {
		HTTPClient: http.DefaultClient,
	}
}

func (c *Client) call(path string, params url.Values) (map[string] interface{}, error) {
	// url:= "http://codereview.pickme.cn/" + path
	urlStr:= "http://localhost:3005/" + path

	req, err := http.NewRequest("POST", urlStr, bytes.NewBufferString(params.Encode()));
	req.Header.Set("Content-Type", "application/x-www-form-urlencoded")
	if err != nil {
		return nil, err
	}
	body, doErr := c.do(req)
	if doErr != nil  {
		return nil, doErr
	}
    defer body.Close()

	var dat map[string]interface{}

	jsonErr := json.NewDecoder(body).Decode(&dat);
    if jsonErr != nil {
    	return nil, doErr
    }
    return dat, nil
}

func (c *Client) callData(path string, params url.Values) (map[string] interface{}) {
	res, err := c.call(path, params)
	if err != nil {
		panic(err)
	}
	fmt.Println(res)
	if (int(res["resultCode"].(float64)) != 0) {
		panic("resultCode is not 0")
	}
	data := res["resultData"].(map[string]interface{})
	return data
}

// perform the request.
func (c *Client) do(req *http.Request) (io.ReadCloser, error) {
	res, err := c.HTTPClient.Do(req)
	if err != nil {
		return nil, err
	}

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

	if err := json.NewDecoder(res.Body).Decode(e); err != nil {
		return nil, err
	}

	return nil, e
}

func readString(reader io.ReadCloser) (string){
	buf := new(bytes.Buffer)
	buf.ReadFrom(reader)
	s := buf.String()
	return s
}

func TestLogin(t *testing.T) {
	c := NewClient()
	res := c.callData("user/login", url.Values{"mobilePhoneNumber": {"13261630925"}, 
		"password": {"123456"}});
	assert.Equal(t, "lzwjava", res["username"])
	assert.Equal(t, "13261630925", res["mobilePhoneNumber"])
}

