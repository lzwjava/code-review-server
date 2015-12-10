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

func TestRegisterAndLogin(t *testing.T) {
	deleteUser("1326163092")
	c := NewClient()
	res := c.callData("user/register", url.Values{"mobilePhoneNumber": {"1326163092"},
		"username": {"lzwjavaTest"}, "smsCode": {"5555"}, "password":{"123456"}, "type": {"0"}})
	assert.Equal(t, "lzwjavaTest", res["username"])
	assert.NotNil(t, res["id"])
	assert.NotNil(t, res["created"])
	assert.Equal(t, toInt(res["type"]), 0)

	res = c.callData("user/login", url.Values{"mobilePhoneNumber": {"1326163092"}, 
		"password": {"123456"}});
	assert.Equal(t, "lzwjavaTest", res["username"])
	assert.Equal(t, "1326163092", res["mobilePhoneNumber"])

	avatarUrl := "http://7xotd0.com1.z0.glb.clouddn.com/header_logo.png"

	res = c.callData("user/update", url.Values{"username": {"lzwjavaTest1"},
		"avatarUrl": {avatarUrl}});
	assert.Equal(t, "lzwjavaTest1", res["username"])
	assert.Equal(t, avatarUrl, res["avatarUrl"])

	deleteUser("1326163092")
}

func TestReviewerRegisterAndLogin(t *testing.T) {
	deleteUser("13261630924")
	c := NewClient()
	res := c.callData("user/register", url.Values{"mobilePhoneNumber": {"13261630924"},
		"username": {"lzwjavaReviewer"}, "smsCode": {"5555"}, "password":{"123456"}, "type": {"1"}})

	res = c.callData("user/update", url.Values{"introduction": {"I'm lzwjava"},
		"experience": {"1"}})
	assert.Equal(t, "I'm lzwjava", res["introduction"])
	assert.Equal(t, 1, toInt(res["experience"]))

	result, _ := c.call("user/update", url.Values{"experience": {"100"}})
	assert.Equal(t, toInt(result["resultCode"]), 15)

	deleteUser("13261630924")
}
