package codereview

import (
	"testing"
	_ "github.com/stretchr/testify/assert"
	_ "fmt"
	_ "net/url"
	"github.com/stretchr/testify/assert"
	"net/url"
)

func TestReviews_AddReview(t *testing.T) {
	c := NewClient()
	reviewer, _, order := addOrder(c, t)

	c.sessionToken = reviewer["sessionToken"].(string)
	orderId := floatToStr(order["orderId"])
	reviewRes := c.callData("reviews/add", url.Values{"orderId": {orderId},
		"content": {"代码写得不错！"}, "title":{"记一次动画效果"}})
	assert.NotNil(t, reviewRes["reviewId"])
	assert.Equal(t, "代码写得不错！", reviewRes["content"])
	assert.Equal(t, "记一次动画效果", reviewRes["title"])
}

func TestReviews_Duplicate(t *testing.T) {
	c := NewClient()
	reviewer, _, order := addOrder(c, t)

	c.sessionToken = reviewer["sessionToken"].(string)
	orderId := floatToStr(order["orderId"])
	reviewRes := c.callData("reviews/add", url.Values{"orderId": {orderId},
		"content": {"代码写得不错！"}, "title":{"标题"}})
	assert.NotNil(t, reviewRes["reviewId"])

	reviewRes = c.call("reviews/add", url.Values{"orderId": {orderId},
		"content": {"代码写得不错！"}, "title":{"标题"}})
	assert.Equal(t, 18, toInt(reviewRes["code"]))
}

func TestReviews_EditReview(t *testing.T) {
	c := NewClient()
	reviewer, _, order := addOrder(c, t)

	c.sessionToken = reviewer["sessionToken"].(string)
	orderId := floatToStr(order["orderId"])
	reviewRes := c.callData("reviews/add", url.Values{"orderId": {orderId},
		"content": {"代码写得不错！"}, "title":{"标题"}})
	assert.NotNil(t, reviewRes["reviewId"])
	assert.Equal(t, "代码写得不错！", reviewRes["content"])

	reviewId := floatToStr(reviewRes["reviewId"])
	editRes := c.callData("reviews/edit", url.Values{"reviewId": {reviewId},
		"content": {"这里有几个问题。"}, "title":{"新标题"}})
	assert.Equal(t, "这里有几个问题。", editRes["content"])
	assert.Equal(t, "新标题", editRes["title"])
}
