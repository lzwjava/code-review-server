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
	c := NewClient()
	addOrder(c, t)
}

func TestOrders_All(t *testing.T) {
	c := NewClient()
	addOrder(c, t)
	res := c.getArrayData("orders", url.Values{"status":{"unpaid"}});
	assert.Equal(t, 1, len(res))

	res = c.getArrayData("orders", url.Values{});
	assert.Equal(t, 1, len(res))
}

func TestOrders_View(t *testing.T) {
	cleanTables()

	c := NewClient()
	reviewer, _ := registerUsers(c)
	reviewerId := reviewer["id"].(string);

	order := c.callData("orders/add", url.Values{"gitHubUrl": {"https://github.com/lzwjava/Reveal-In-GitHub"},
		"remark": {"麻烦大神了"}, "reviewerId":{reviewerId}, "codeLines":{"3500"}});

	orderId := floatToStr(order["orderId"])
	theOrder := c.getData("orders/view", url.Values{"orderId": {orderId}})

	assert.Equal(t, floatToStr(theOrder["orderId"]), orderId)
	assert.Equal(t, theOrder["reviewerId"].(string), reviewerId)
}

func addOrder(c *Client, t *testing.T) (map[string]interface{}, map[string]interface{}, map[string]interface{}) {
	cleanTables()
	reviewer, learner := registerUsers(c)

	reviewerId := reviewer["id"].(string)
	learnerId := learner["id"].(string)

	order := c.callData("orders/add", url.Values{"gitHubUrl": {"https://github.com/lzwjava/Reveal-In-GitHub"},
		"remark": {"麻烦大神了"}, "reviewerId":{reviewerId}, "codeLines":{"3000"}})
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
	return reviewer, learner, order
}

func addOrderAndReward(c *Client, t *testing.T) (map[string]interface{}, map[string]interface{}, map[string]interface{}) {
	reviewer, learner, order := addOrder(c, t)
	reward(c, floatToStr(order["orderId"]), t)
	return reviewer, learner, order
}

func TestOrders_maxOrder(t *testing.T) {
	cleanTables()
	c := NewClient()
	reviewer := registerReviewer(c)
	res := c.callData("user/update", url.Values{"maxOrders":{"0"}})
	assert.Equal(t, 0, toInt(res["maxOrders"]))

	registerLearner(c)

	reviewerId := reviewer["id"].(string)

	order := c.call("orders/add", url.Values{"gitHubUrl": {"https://github.com/lzwjava/Reveal-In-GitHub"},
		"remark": {"麻烦大神了"}, "reviewerId":{reviewerId}, "codeLines":{"1000"}})
	assert.Equal(t, 20, toInt(order["code"]))
}

func TestOrders_Consent(t *testing.T) {
	c := NewClient()
	reviewer, _, order := addOrderAndReward(c, t)
	c.sessionToken = reviewer["sessionToken"].(string)
	orderId := floatToStr(order["orderId"])
	res := c.callData("orders/" + orderId, url.Values{"status":{"consented"}, "orderId":{orderId}})
	assert.NotNil(t, res)
	theOrder := c.getData("orders/view", url.Values{"orderId": {orderId}})
	assert.NotNil(t, theOrder)
	assert.Equal(t, "consented", theOrder["status"])
}
