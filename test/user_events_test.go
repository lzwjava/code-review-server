package codereview

import (
	"testing"
	"net/url"
	"github.com/stretchr/testify/assert"
)

func TestUserEvents_one(t *testing.T) {
	setUp()
	c := NewClient()
	registerLearner(c)
	eventId, _ := addAndAtendEvent(c)
	userEvent := c.getData("user/events/" + eventId, url.Values{})
	assert.NotNil(t, userEvent);
}

func TestUserEvents_list(t *testing.T) {
	setUp()
	c := NewClient()
	registerLearner(c)
	eventId, _ := addAndAtendEvent(c)
	userEvents := c.getArrayData("user/events", url.Values{})
	assert.NotNil(t, userEvents);
	userEvent := userEvents[0].(map[string]interface{})
	assert.Equal(t, floatToStr(userEvent["eventId"]), eventId)
}
