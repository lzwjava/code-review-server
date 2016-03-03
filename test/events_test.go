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
	_, ok := event["attendanceId"]
	assert.True(t, ok);
}
