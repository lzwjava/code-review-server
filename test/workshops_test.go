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
	payRes := c.post("workshops/" + workshopId + "/pay", url.Values{})
	assert.NotNil(t, payRes)

	orderNo := payRes["order_no"].(string)
	callbackRes := c.postWithStr("rewards/callback", workshopCallbackStr(orderNo, workshopId, learnerId, 5000))
	assert.Equal(t, toInt(callbackRes["code"]), 0);
}

func workshopCallbackStr(orderNo string, eventId string, userId string, amount int) string {
	meta := map[string]interface{}{"workshopId": eventId, "userId":userId}
	return callbackStr(orderNo, meta, amount);
}
