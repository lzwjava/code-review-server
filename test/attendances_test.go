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
	eventId := addEventAndPay(c)
	attendances := c.getArrayData("attendances", url.Values{})
	assert.NotNil(t, attendances);
	attendance := attendances[0].(map[string]interface{})
	assert.Equal(t, floatToStr(attendance["eventId"]), eventId)
}
