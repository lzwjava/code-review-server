package codereview

import (
	"github.com/stretchr/testify/assert"
	"testing"
	"net/url"
)

func TestWorkshops_add(t *testing.T) {
	setUp()
	c := NewClient()
	workshopId := addWorkshop(c)
	assert.NotNil(t, workshopId)
}

func addWorkshop(c *Client) string {
	res := c.postData("workshops", url.Values{"name":{"第一期研讨班"}, "amount":{"2000000"},
		"maxPeople":{"20"}})
	return floatToStr(res["workshopId"])
}

func TestWorkshops_getOne(t *testing.T) {
	setUp()
	c := NewClient()
	workshopId := addWorkshop(c)
	res := c.getData("workshops/" + workshopId, url.Values{})
	assert.NotNil(t, res)
	assert.NotNil(t, res["workshopId"])
	assert.NotNil(t, res["name"])
	assert.NotNil(t, res["maxPeople"])
	assert.NotNil(t, res["created"])
}

func TestWorkshops_pay(t *testing.T) {
	setUp()
	c := NewClient()
	learner := registerLearner(c)
	learnerId := learner["id"].(string)
	workshopId := addWorkshop(c)
	payRes := payWorkshop(c, workshopId, learnerId)
	assert.Equal(t, toInt(payRes["code"]), 0);
}

func workshopCallbackStr(orderNo string, eventId string, userId string, amount int) string {
	meta := map[string]interface{}{"workshopId": eventId, "userId":userId}
	return callbackStr(orderNo, meta, amount);
}

func payWorkshop(c *Client, workshopId string, userId string) map[string]interface{} {
	payRes := c.post("workshops/" + workshopId + "/pay", url.Values{})
	orderNo := payRes["order_no"].(string)
	return c.postWithStr("rewards/callback", workshopCallbackStr(orderNo, workshopId, userId, 5000))
}

func addWorkshopAndPay(c *Client) string {
	learner := registerLearner(c)
	learnerId := learner["id"].(string)
	workshopId := addWorkshop(c)
	payWorkshop(c,workshopId,learnerId)
	return workshopId
}
