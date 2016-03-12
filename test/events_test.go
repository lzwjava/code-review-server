package codereview

import (
	"testing"
	"net/url"
	"github.com/stretchr/testify/assert"
)

func TestEvents_add(t *testing.T) {
	setUp()
	c := NewClient()
	res := c.postData("events", url.Values{"name":{"CRViewController 交流会"}, "amount":{"20000"},
		"maxPeople":{"50"}, "location": {"中关村创业大街 3W 咖啡二层"}, "startDate":{"2016-03-13 13:00"}})
	assert.NotNil(t, res);
	assert.NotNil(t, res["eventId"])
}

func addEventWithPeople(c *Client, maxPeople int) string {
	res := c.postData("events", url.Values{"name":{"CRViewController 交流会"}, "amount":{"20000"},
		"maxPeople":{floatToStr(maxPeople)}, "location": {"中关村创业大街 3W 咖啡二层"},
		"startDate":{"2016-03-13 13:00"}})
	return floatToStr(res["eventId"])
}

func addEvent(c *Client) string {
	return addEventWithPeople(c, 50)
}

func addEventAndPay(c *Client) string {
	user := registerReviewer(c)
	eventId := addEvent(c)
	payEvent(c, eventId, user["id"].(string))
	return eventId
}

func TestEvents_get(t *testing.T) {
	setUp()
	c := NewClient()
	eventId := addEventAndPay(c)
	event := c.getData("events/" + eventId, url.Values{})
	assert.NotNil(t, event)
	assert.NotNil(t, event["amount"])
	assert.NotNil(t, event["eventId"])
	assert.NotNil(t, event["name"])
	assert.NotNil(t, event["created"])
	assert.NotNil(t, event["restCount"])
	assert.NotNil(t, event["attendCount"])
	assert.NotNil(t, event["maxPeople"])
	assert.NotNil(t, event["startDate"])
	assert.NotNil(t, event["location"])
	_, ok := event["attendance"]
	assert.True(t, ok);
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

func TestEvents_payExceed(t *testing.T) {
	setUp()
	c := NewClient()
	registerLearner(c)
	eventId := addEventWithPeople(c, 0)
	payRes := c.post("events/" + eventId + "/pay", url.Values{})
	assert.NotNil(t, payRes)
	assert.Equal(t, toInt(payRes["code"]), 20)
}

func TestEvents_notifyComing(t *testing.T) {
	setUp()
	c := NewClient()
	eventId := addEventAndPay(c)
	res := c.getData("admin/events/" + eventId + "/notifyComing", url.Values{})
	assert.NotNil(t, res)
}
