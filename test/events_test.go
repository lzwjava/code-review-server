package codereview

import (
	"testing"
	"net/url"
	"github.com/stretchr/testify/assert"
)

func TestEvents_add(t *testing.T) {
	setUp()
	c := NewClient()
	res := c.postData("events", url.Values{"name":{"3月12号线下活动"}, "amount":{"20000"}, "maxPeople":{"50"}})
	assert.NotNil(t, res);
	assert.NotNil(t, res["eventId"])
}

func addEvent(c *Client) string {
	res := c.postData("events", url.Values{"name":{"3月12号线下活动"}, "amount":{"20000"}, "maxPeople":{"50"}})
	return floatToStr(res["eventId"])
}

func addEventAndPay(c *Client, user map[string]interface{}) string {
	eventId := addEvent(c)
	payEvent(c, eventId, user["id"].(string))
	return eventId
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
	assert.Equal(t, "none", event["status"])
	_, ok := event["attendance"]
	assert.True(t, ok);
}
