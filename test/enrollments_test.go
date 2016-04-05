package codereview

import (
	"github.com/stretchr/testify/assert"
	"testing"
	"net/url"
)

func TestEnrollments_listByWorkshop(t *testing.T) {
	setUp()
	c := NewClient()
	workshopId := addWorkshopAndPay(c)
	enrollments := c.getArrayData("workshops/" + workshopId + "/enrollments", url.Values{})
	assert.NotNil(t, enrollments)
	assert.Equal(t, len(enrollments), 1)
	enrollment := enrollments[0].(map[string]interface{})
	assert.NotNil(t, enrollment["user"])
	assert.NotNil(t, enrollment["workshop"])
}
