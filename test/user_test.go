package codereview

import (
	"testing"
	"github.com/stretchr/testify/assert"
	"fmt"
	_ "reflect"
	"net/url"
)

func unused1() {
	fmt.Printf("")
}

func deleteUser(mobilePhoneNumber string) {
	c := NewClient()
	_, err := c.call("user/delete", url.Values{"mobilePhoneNumber": {mobilePhoneNumber}})
	if err != nil {
		panic(err)
	}
}

func TestRegisterAndLogin(t *testing.T) {
	deleteUser("1326163092")
	c := NewClient()
	res := c.callData("user/register", url.Values{"mobilePhoneNumber": {"1326163092"},
		"username": {"lzwjavaTest"}, "smsCode": {"5555"}, "password":{"123456"}, "type": {"0"}})
	assert.Equal(t, "lzwjavaTest", res["username"])
	assert.NotNil(t, res["id"])
	assert.NotNil(t, res["created"])
	assert.Equal(t, int(res["type"].(float64)), 0)

	res = c.callData("user/login", url.Values{"mobilePhoneNumber": {"1326163092"}, 
		"password": {"123456"}});
	assert.Equal(t, "lzwjavaTest", res["username"])
	assert.Equal(t, "1326163092", res["mobilePhoneNumber"])

	deleteUser("1326163092")
}
