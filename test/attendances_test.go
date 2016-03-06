package codereview

import (
	"testing"
	"net/url"
	"github.com/stretchr/testify/assert"
)

//func TestAttendances_one(t *testing.T) {
//	setUp()
//	c := NewClient()
//	learner := registerLearner(c)
//	eventId := addEventAndPay(c, learner)
//
//
//	attendance := c.getData("attendances/" + eventId, url.Values{})
//	assert.NotNil(t, attendance);
//}

func TestAttendances_list(t *testing.T) {
	setUp()
	c := NewClient()
	user := registerLearner(c)
	eventId := addEventAndPay(c, user)
	attendances := c.getArrayData("attendances", url.Values{})
	assert.NotNil(t, attendances);
	attendance := attendances[0].(map[string]interface{})
	assert.Equal(t, floatToStr(attendance["eventId"]), eventId)
}

func payEvent(c *Client, eventId string, userId string) {
	payRes := c.post("events/" + eventId + "/pay", url.Values{})
	orderNo := payRes["order_no"].(string)
	c.postWithStr("rewards/callback", eventCallbackStr(orderNo, eventId, userId, 5000))
}

func TestEvents_pay(t *testing.T) {
	setUp()
	c := NewClient()
	learner := registerLearner(c)
	learnerId := learner["id"].(string)
	eventId := addEvent(c)
	payRes := c.post("events/" + eventId + "/pay", url.Values{})
	assert.NotNil(t, payRes)

	orderNo := payRes["order_no"].(string)
	callbackRes := c.postWithStr("rewards/callback", eventCallbackStr(orderNo, eventId, learnerId, 5000))
	assert.Equal(t, toInt(callbackRes["code"]), 0);
}

func eventCallbackStr(orderNo string, eventId string, userId string, amount int) string {
	meta := map[string]interface{}{"eventId": eventId, "userId":userId}
	return callbackStr(orderNo, meta, amount);
}
