package codereview
import (
	"testing"
	"net/url"
	"github.com/stretchr/testify/assert"
)

func TestNotifications_list(t *testing.T) {
	setUp()
	c := NewClient()
	_, learner, _, _, _ := addReviewAndComment(c)
	c.sessionToken = learner["sessionToken"].(string)
	res := c.getArrayData("notifications", url.Values{})
	assert.True(t, len(res) > 0)
	notification := res[0].(map[string]interface{})
	assert.NotNil(t, notification["notificationId"])
	assert.Equal(t, "comment", notification["type"])
	assert.NotNil(t, notification["comment"])
	assert.NotNil(t, notification["userId"])
	assert.NotNil(t, notification["unread"])
}

func TestNotifications_mark(t *testing.T) {
	setUp()
	c := NewClient()
	_, learner, _, _, _ := addReviewAndComment(c)
	c.sessionToken = learner["sessionToken"].(string)
	res := c.patchData("notifications", url.Values{})
	assert.NotNil(t, res)

	array := c.getArrayData("notifications", url.Values{"unread": {"1"}})
	assert.Equal(t, len(array), 0)
}

func TestNotifications_markOne(t *testing.T) {
	setUp()
	c := NewClient()
	_, learner, _, _, _ := addReviewAndComment(c)
	c.sessionToken = learner["sessionToken"].(string)

	res := c.getArrayData("notifications", url.Values{})
	assert.True(t, len(res) > 0)

	notification := res[0].(map[string]interface{})
	notificationId := floatToStr(notification["notificationId"])
	patchRes := c.patchData("notifications/" + notificationId, url.Values{})
	assert.NotNil(t, patchRes)

	array := c.getArrayData("notifications", url.Values{"unread": {"1"}})
	assert.Equal(t, len(array), 0)
}
