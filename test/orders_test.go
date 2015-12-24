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

func TestOrders_View(t *testing.T) {
	cleanTables()

	c := NewClient()
	reviewer, _ := registerUsers(c)
	reviewerId := reviewer["id"].(string);

	order := c.callData("orders/add", url.Values{"gitHubUrl": {"https://github.com/lzwjava/Reveal-In-GitHub"},
		"remark": {"麻烦大神了"}, "reviewerId":{reviewerId}});

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
		"remark": {"麻烦大神了"}, "reviewerId":{reviewerId}})
	assert.Equal(t, "https://github.com/lzwjava/Reveal-In-GitHub", order["gitHubUrl"])
	assert.Equal(t, "麻烦大神了", order["remark"].(string))
	assert.Equal(t, reviewerId, order["reviewerId"])
	assert.Equal(t, learnerId, order["learnerId"])
	assert.Equal(t, 0, toInt(order["status"]))
	assert.NotNil(t, order["created"])
	assert.NotNil(t, order["updated"])
	assert.NotNil(t, order["orderId"])
	assert.Nil(t, order["reviewId"])
	return reviewer, learner, order
}
