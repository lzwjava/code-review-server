package codereview

import (
	"testing"
	"github.com/stretchr/testify/assert"
	_ "fmt"
	_ "reflect"
	"net/url"
	"time"
)

func TestUser_RegisterAndLogin(t *testing.T) {
	cleanTables()

	c := NewClient()
	md5Str := md5password("123456")
	res := c.callData("user/register", url.Values{"mobilePhoneNumber": {"1326163092"},
		"username": {"lzwjavaTest"}, "smsCode": {"5555"}, "password":{md5Str}, "type": {"0"}})
	assert.Equal(t, "lzwjavaTest", res["username"])
	assert.NotNil(t, res["id"])
	assert.NotNil(t, res["created"])
	assert.NotNil(t, res["updated"]);
	assert.Equal(t, toInt(res["type"]), 0)

	res = c.callData("user/login", url.Values{"mobilePhoneNumber": {"1326163092"},
		"password": {md5password("123456")}});
	assert.Equal(t, "lzwjavaTest", res["username"])
	assert.Equal(t, "1326163092", res["mobilePhoneNumber"])
}

func TestUser_Update(t *testing.T) {
	c := NewClient()
	learner := registerLearner(c)
	updated := learner["updated"].(string)
	avatarUrl := "http://7xotd0.com1.z0.glb.clouddn.com/header_logo.png"

	time.Sleep(1000 * time.Millisecond)

	res := c.callData("user/update", url.Values{"username": {"lzwjavaTest1"},
		"avatarUrl": {avatarUrl}, "company":{"LeanCloud"},
		"jobTitle": {"iOS工程师"}, "gitHubUsername": {"lzwjava"}, "introduction": {"一只 iOS 菜鸟"}})

	assert.Equal(t, "lzwjavaTest1", res["username"])
	assert.Equal(t, avatarUrl, res["avatarUrl"])
	assert.Equal(t, "LeanCloud", res["company"])
	assert.Equal(t, "iOS工程师", res["jobTitle"])
	assert.Equal(t, "lzwjava", res["gitHubUsername"])
	assert.Equal(t, "一只 iOS 菜鸟", res["introduction"])
	assert.NotEqual(t, updated, res["updated"].(string))

	// Same username
	res = c.callData("user/update", url.Values{"username": {"lzwjavaTest1"}});
	assert.Equal(t, "lzwjavaTest1", res["username"]);
}

func TestUser_ReviewerUpdate(t *testing.T) {
	c := NewClient()
	registerReviewer(c)

	res := c.callData("user/update", url.Values{"maxOrders":{"7"}})
	assert.Equal(t, 7, toInt(res["maxOrders"]));
}

func TestUser_ReviewerRegisterAndLogin(t *testing.T) {
	cleanTables()

	c := NewClient()
	res := c.callData("user/register", url.Values{"mobilePhoneNumber": {"13261630924"},
		"username": {"lzwjavaReviewer"}, "smsCode": {"5555"}, "password":{md5password("123456")}, "type": {"1"}})

	res = c.callData("user/update", url.Values{"introduction": {"I'm lzwjava"},
		"experience": {"1"}})
	assert.Equal(t, "I'm lzwjava", res["introduction"])
	assert.Equal(t, 1, toInt(res["experience"]))

	result := c.call("user/update", url.Values{"experience": {"100"}})
	assert.Equal(t, toInt(result["code"]), 15)
}

func TestUser_Self(t *testing.T) {
	c := NewClient()
	_, learner := registerUsers(c)
	self := c.getData("user/self", url.Values{})
	assert.Equal(t, self["id"].(string), learner["id"].(string))
	assert.Equal(t, self["username"].(string), learner["username"].(string))
}
