package codereview

import (
	"testing"
	"net/url"
	"github.com/stretchr/testify/assert"
)

func TestEvents_add(t *testing.T) {
	setUp()
	c := NewClient()
	res := c.postData("events", url.Values{"name":{"3月12号线下活动"}, "amount":{"20000"}})
	assert.NotNil(t, res);
	assert.NotNil(t, res["eventId"])
}

func addEvent(c *Client) string {
	res := c.postData("events", url.Values{"name":{"3月12号线下活动"}, "amount":{"20000"}})
	return floatToStr(res["eventId"])
}

func TestEvents_attend(t *testing.T) {
	setUp()
	c := NewClient()
	registerLearner(c)
	eventId := addEvent(c)
	res := c.postData("events/" + eventId + "/attend", url.Values{})
	assert.NotNil(t, res)
	assert.NotNil(t, res["userEventId"])
}

func attendEvent(c *Client, eventId string) string {
	res := c.postData("events/" + eventId + "/attend", url.Values{})
	return floatToStr(res["userEventId"])
}

func addAndAttendEvent(c *Client) (string, string) {
	eventId := addEvent(c)
	userEventId := attendEvent(c, eventId)
	return eventId, userEventId;
}

func TestEvents_pay(t *testing.T) {
	setUp()
	c := NewClient()
	registerLearner(c)
	eventId := addEvent(c)
	userEventId := attendEvent(c, eventId)
	payRes := c.post("events/" + eventId + "/pay", url.Values{})
	assert.NotNil(t, payRes)

	orderNo := payRes["order_no"].(string)
	callbackRes := c.postWithStr("rewards/callback", eventCallbackStr(orderNo, userEventId, 5000))
	assert.Equal(t, toInt(callbackRes["code"]), 0);
}

func eventCallbackStr(orderNo string, userEventId string, amount int) string {
	return callbackStr(orderNo, "\"userEventId\":" + userEventId, amount);
}

func TestEvents_get(t *testing.T) {
	setUp()
	c := NewClient()
	eventId := addEvent(c)
	event := c.getData("events/" + eventId, url.Values{})
	assert.NotNil(t, event)
	assert.NotNil(t, event["amount"])
	assert.NotNil(t, event["eventId"])
	assert.NotNil(t, event["name"])
	assert.NotNil(t, event["created"])
	assert.NotNil(t, event["userEventId"]);
}
