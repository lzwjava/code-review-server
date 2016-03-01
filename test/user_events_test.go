package codereview

import (
	"testing"
	"net/url"
	"github.com/stretchr/testify/assert"
)

func TestUserEvents_add(t *testing.T) {
	setUp()
	c := NewClient()
	registerLearner(c)
	eventId := addEvent(c)
	res := c.postData("user/events", url.Values{"eventId": {eventId}})
	assert.NotNil(t, res)
}
