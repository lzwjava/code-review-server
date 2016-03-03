package codereview

import (
	"testing"
	"net/url"
	"github.com/stretchr/testify/assert"
)

func TestAttendances_one(t *testing.T) {
	setUp()
	c := NewClient()
	registerLearner(c)
	eventId, _ := addAndAttendEvent(c)
	attendance := c.getData("attendances/" + eventId, url.Values{})
	assert.NotNil(t, attendance);
}

func TestAttendances_list(t *testing.T) {
	setUp()
	c := NewClient()
	registerLearner(c)
	eventId, _ := addAndAttendEvent(c)
	attendances := c.getArrayData("attendances", url.Values{})
	assert.NotNil(t, attendances);
	attendance := attendances[0].(map[string]interface{})
	assert.Equal(t, floatToStr(attendance["eventId"]), eventId)
}

func TestAttendances_create(t *testing.T) {
	setUp()
	c := NewClient()
	registerLearner(c)
	eventId := addEvent(c)
	res := c.postData("attendances", url.Values{"eventId": {eventId}})
	assert.NotNil(t, res)
	assert.NotNil(t, res["attendanceId"])
}

func attendEvent(c *Client, eventId string) string {
	res := c.postData("attendances", url.Values{"eventId": {eventId}})
	return floatToStr(res["attendanceId"])
}

func addAndAttendEvent(c *Client) (string, string) {
	eventId := addEvent(c)
	attendanceId := attendEvent(c, eventId)
	return eventId, attendanceId;
}

func TestAttendances_pay(t *testing.T) {
	setUp()
	c := NewClient()
	registerLearner(c)
	eventId := addEvent(c)
	attendanceId := attendEvent(c, eventId)
	payRes := c.post("attendances/" + attendanceId + "/pay", url.Values{})
	assert.NotNil(t, payRes)

	orderNo := payRes["order_no"].(string)
	callbackRes := c.postWithStr("rewards/callback", eventCallbackStr(orderNo, attendanceId, 5000))
	assert.Equal(t, toInt(callbackRes["code"]), 0);
}

func eventCallbackStr(orderNo string, attendanceId string, amount int) string {
	return callbackStr(orderNo, "\"attendanceId\":" + attendanceId, amount);
}
