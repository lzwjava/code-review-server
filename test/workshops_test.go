package codereview

import (
	"github.com/stretchr/testify/assert"
	"testing"
	"net/url"
)

func TestWorkshops_add(t *testing.T) {
	setUp()
	c := NewClient()
	workshopId := addWorkshop(c)
	assert.NotNil(t, workshopId)
}

func addWorkshop(c *Client) string {
	res := c.postData("workshops", url.Values{"name":{"第一期研讨班"}, "amount":{"2000000"},
		"maxPeople":{"20"}})
	return floatToStr(res["workshopId"])
}

func TestWorkshops_getOne(t *testing.T) {
	setUp()
	c := NewClient()
	workshopId := addWorkshop(c)
	res := c.getData("workshops/" + workshopId, url.Values{})
	assert.NotNil(t, res)
	assert.NotNil(t, res["workshopId"])
	assert.NotNil(t, res["name"])
	assert.NotNil(t, res["maxPeople"])
	assert.NotNil(t, res["created"])
}
