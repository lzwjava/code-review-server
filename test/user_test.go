package codereview

import (
	"testing"
	"github.com/stretchr/testify/assert"
	_ "fmt"
	_ "reflect"
	"net/url"
	"time"
	"fmt"
)

func TestUser_RegisterAndLogin(t *testing.T) {
	setUp()

	c := NewClient()
	md5Str := md5password("123456")
	res := c.postData("user/register", url.Values{"mobilePhoneNumber": {"18928980893"},
		"username": {"lzwjavaTest"}, "smsCode": {"5555"}, "password":{md5Str}, "type": {"learner"}})
	assert.Equal(t, "lzwjavaTest", res["username"])
	assert.NotNil(t, res["id"])
	assert.NotNil(t, res["created"])
	assert.NotNil(t, res["updated"]);
	assert.Equal(t, "learner", res["type"])
	assert.NotNil(t, res["tags"]);

	res = c.postData("user/login", url.Values{"mobilePhoneNumber": {"18928980893"},
		"password": {md5password("123456")}});
	assert.Equal(t, "lzwjavaTest", res["username"])
	assert.Equal(t, "18928980893", res["mobilePhoneNumber"])
}

func TestUser_Update(t *testing.T) {
	setUp()
	c := NewClient()
	learner := registerLearner(c)
	updated := learner["updated"].(string)
	avatarUrl := "http://7xotd0.com1.z0.glb.clouddn.com/header_logo.png"

	time.Sleep(time.Second)

	res := c.patchData("user", url.Values{"username": {"lzwjavaTest1"},
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
	res = c.patchData("user", url.Values{"username": {"lzwjavaTest1"}});
	assert.Equal(t, "lzwjavaTest1", res["username"]);
}

func TestUser_ReviewerUpdate(t *testing.T) {
	setUp()
	c := NewClient()
	reviewer := registerReviewer(c)
	assert.NotNil(t, reviewer["maxOrders"])

	res := c.patchData("user", url.Values{"maxOrders":{"7"}})
	assert.Equal(t, 7, toInt(res["maxOrders"]));
}

func TestUser_ReviewerRegisterAndLogin(t *testing.T) {
	setUp()

	c := NewClient()

	res := c.postData("user/register", url.Values{"mobilePhoneNumber": {"13261630924"},
		"username": {"lzwjavaReviewer2"}, "smsCode": {"5555"}, "password":{md5password("123456")}, "type":
		{"reviewer"}})

	res = c.postData("user/register", url.Values{"mobilePhoneNumber": {"13261630926"},
		"username": {"lzwjavaReviewer1"}, "smsCode": {"5555"}, "password":{md5password("123456")}, "type":
		{"reviewer"}})

	res = c.postData("user/register", url.Values{"mobilePhoneNumber": {"13261630925"},
		"username": {"lzwjavaReviewer"}, "smsCode": {"5555"}, "password":{md5password("123456")}, "type": {"reviewer"}})

	res = c.patchData("user", url.Values{"introduction": {"I'm lzwjava"},
		"experience": {"1"}})
	assert.Equal(t, "I'm lzwjava", res["introduction"])
	assert.Equal(t, 1, toInt(res["experience"]))

	result := c.patch("user", url.Values{"experience": {"100"}})
	assert.Equal(t, toInt(result["code"]), 15)
}

func TestUser_Self(t *testing.T) {
	setUp()
	c := NewClient()
	_, learner := registerUsers(c)
	self := c.getData("user/self", url.Values{})
	assert.Equal(t, self["id"].(string), learner["id"].(string))
	assert.Equal(t, self["username"].(string), learner["username"].(string))
}

func TestUser_SelfWithSessionToken(t *testing.T) {
	setUp()
	c := NewClient()
	reviewer, _ := registerUsers(c)

	newC := NewClient()
	self := newC.getData("user/self", url.Values{"sessionToken": {reviewer["sessionToken"].(string)}})
	assert.Equal(t, self["id"], reviewer["id"])
}

func TestUser_requestSmsCode(t *testing.T) {
	setUp()
	c := NewClient()
	res := c.post("user/requestSmsCode", url.Values{"mobilePhoneNumber": {"xx"}})
	fmt.Println(res)
}

func TestUser_requestResetPassword(t *testing.T) {
	setUp()
	c := NewClient()
	reviewer := registerReviewer(c)
	phone := reviewer["mobilePhoneNumber"].(string)
	res := c.post("user/requestResetPassword",
		url.Values{"mobilePhoneNumber":{phone + "xx"}})
	assert.NotNil(t, res);
	assert.Equal(t, 8, toInt(res["code"]));
}

func TestUser_resetPassword(t *testing.T) {
	setUp()
	c := NewClient()
	reviewer := registerReviewer(c)
	phone := reviewer["mobilePhoneNumber"].(string)

	newUser := c.postData("user/resetPassword", url.Values{"mobilePhoneNumber": {phone}, "smsCode":{"5555"},
		"password": {md5password("new123456")}})
	assert.NotNil(t, newUser)
	assert.NotEqual(t, newUser["sessionToken"], reviewer["sessionToken"])

	res := login(c, phone, "new123456")
	assert.NotNil(t, res)
}
