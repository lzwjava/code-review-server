package codereview

import (
	"testing"
	"net/url"
	"github.com/stretchr/testify/assert"
)

func TestEvents_add(t *testing.T) {
	setUp()
	c := NewClient()
	res := c.postData("events", url.Values{"name":{"3月12号线下活动"}})
	assert.NotNil(t, res);
	assert.NotNil(t, res["eventId"])
}

func addEvent(c *Client) string {
	res := c.postData("events", url.Values{"name":{"3月12号线下活动"}})
	return floatToStr(res["eventId"])
}
