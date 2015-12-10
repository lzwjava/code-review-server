package codereview

import (
	"testing"
	"github.com/stretchr/testify/assert"
	_ "fmt"
	"net/url"
	"strconv"
)

func TestOrders_Add(t *testing.T) {
	c := NewClient()

	reviewer := registerReviewer(c)
	learner := registerLearner(c)

	res := c.callData("orders/add", url.Values{"gitHubUrl": {"https://github.com/lzwjava/Reveal-In-GitHub"},
		"remark": {"麻烦大神了"}, "reviewerId": {reviewer["id"].(string)}})
	assert.Equal(t, "https://github.com/lzwjava/Reveal-In-GitHub", res["gitHubUrl"])

	deleteRecord("orders", "orderId", strconv.Itoa(toInt(res["orderId"])))

	deleteUserByData(learner)
	deleteUserByData(reviewer)
}
