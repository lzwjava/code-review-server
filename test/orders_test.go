package codereview

import (
	"testing"
	"github.com/stretchr/testify/assert"
	_ "fmt"
	"net/url"
	_ "encoding/json"
	_ "strings"
	_ "flag"
)

func TestOrders_AddOrder(t *testing.T) {
	setUp()
	c := NewClient()
	reviewer, learner := registerUsers(c)

	reviewerId := reviewer["id"].(string)
	learnerId := learner["id"].(string)

	order := c.postData("orders/add", url.Values{"gitHubUrl": {"https://github.com/lzwjava/Reveal-In-GitHub"},
		"remark": {"麻烦大神了"}, "reviewerId":{reviewerId}, "codeLines":{"3000"}, "amount": {"5000"}})
	assert.Equal(t, "https://github.com/lzwjava/Reveal-In-GitHub", order["gitHubUrl"])
	assert.Equal(t, "麻烦大神了", order["remark"].(string))
	assert.Equal(t, reviewerId, order["reviewerId"])
	assert.Equal(t, learnerId, order["learnerId"])
	assert.Equal(t, "unpaid", order["status"])
	assert.NotNil(t, order["created"])
	assert.NotNil(t, order["updated"])
	assert.NotNil(t, order["orderId"])
	assert.Nil(t, order["reviewId"])
	assert.Equal(t, 3000, toInt(order["codeLines"]))
}

func TestOrders_Amount(t *testing.T) {
	setUp()

	c := NewClient()
	reviewer, _ := registerUsers(c)

	reviewerId := reviewer["id"].(string)

	orderRes := c.post("orders/add", url.Values{"gitHubUrl": {"https://github.com/lzwjava/Reveal-In-GitHub"},
		"remark": {"麻烦大神了"}, "reviewerId":{reviewerId}, "codeLines":{"3000"}, "amount": {"300"}})
	assert.Equal(t, "申请者打赏金额至少为 20 元", orderRes["error"].(string));
	assert.Equal(t, 16, toInt(orderRes["code"]));
}

func TestOrders_NotReturnUnpaid(t *testing.T) {
	setUp()

	c := NewClient()
	reviewer, _, _ := addOrder(c)
	c.sessionToken = reviewer["sessionToken"].(string)
	orders := c.getArrayData("user/orders", url.Values{})
	assert.Equal(t, 0, len(orders))

	orders = c.getArrayData("user/orders", url.Values{"status": {"unpaid"}})
	assert.Equal(t, 1, len(orders))
}

func TestOrders_All(t *testing.T) {
	setUp()
	c := NewClient()
	addOrder(c)
	res := c.getArrayData("user/orders", url.Values{"status":{"unpaid"}});
	assert.Equal(t, 1, len(res))

	res = c.getArrayData("user/orders", url.Values{});
	assert.Equal(t, 1, len(res))
}

func TestOrders_View(t *testing.T) {
	setUp()
	c := NewClient()
	reviewer, _, order := addOrder(c)

	reviewerId := reviewer["id"].(string)

	orderId := floatToStr(order["orderId"])
	theOrder := c.getData("orders/" + orderId, url.Values{})

	assert.Equal(t, floatToStr(theOrder["orderId"]), orderId)
	assert.Equal(t, theOrder["reviewerId"].(string), reviewerId)
}

func TestOrders_maxOrder(t *testing.T) {
	setUp()
	c := NewClient()
	reviewer := registerReviewer(c)
	res := c.patchData("user", url.Values{"maxOrders":{"0"}})
	assert.Equal(t, 0, toInt(res["maxOrders"]))

	registerLearner(c)

	reviewerId := reviewer["id"].(string)

	order := c.post("orders/add", url.Values{"gitHubUrl": {"https://github.com/lzwjava/Reveal-In-GitHub"},
		"remark": {"麻烦大神了"}, "reviewerId":{reviewerId}, "codeLines":{"1000"}, "amount":{"1000"}})
	assert.Equal(t, 20, toInt(order["code"]))
}

func TestOrders_consent(t *testing.T) {
	setUp()
	c := NewClient()
	reviewer, _, order := addOrderAndReward(c)
	c.sessionToken = reviewer["sessionToken"].(string)
	orderId := floatToStr(order["orderId"])
	res := c.postData("orders/" + orderId, url.Values{"status":{"consented"}, "orderId":{orderId}})
	assert.NotNil(t, res)
	theOrder := c.getData("orders/" + orderId, url.Values{})
	assert.NotNil(t, theOrder)
	assert.Equal(t, "consented", theOrder["status"])
}

func TestOrders_reject(t *testing.T) {
	setUp()
	c := NewClient()
	reviewer, _, order := addOrderAndReward(c)
	c.sessionToken = reviewer["sessionToken"].(string)
	orderId := floatToStr(order["orderId"])
	res := c.postData("orders/" + orderId, url.Values{"status": {"rejected"}})
	assert.NotNil(t, res)
	theOrder := c.getData("orders/" + orderId, url.Values{})
	assert.NotNil(t, theOrder)
	assert.Equal(t, "rejected", theOrder["status"])
}

func TestOrders_amount(t *testing.T) {
	setUp()
	c := NewClient()
	_, _, order := addOrderAndReward(c)
	orderId := floatToStr(order["orderId"])
	theOrder := c.getData("orders/" + orderId, url.Values{})
	assert.NotNil(t, theOrder["amount"])
}

func TestOrders_delete(t *testing.T) {
	setUp()
	c := NewClient()
	_, _, order := addOrder(c)
	orderId := floatToStr(order["orderId"])
	res := c.deleteData("orders/" + orderId)
	assert.NotNil(t, res)
}

func TestOrders_deleteFail(t *testing.T) {
	setUp()
	c := NewClient()
	reviewer, _, order := addOrder(c)
	c.sessionToken = reviewer["sessionToken"].(string)
	orderId := floatToStr(order["orderId"])
	res := c.delete("orders/" + orderId)
	assert.NotNil(t, res)
	assert.Equal(t, toInt(res["code"]), 13)
}

func TestOrders_deletePaid(t *testing.T) {
	setUp()
	c := NewClient()
	_, learner, order, _ := addOrderAndReview(c)
	orderId := floatToStr(order["orderId"])
	c.sessionToken = learner["sessionToken"].(string)
	res := c.delete("orders/" + orderId)
	assert.NotNil(t, res)
	assert.Equal(t, toInt(res["code"]), 23)
}
