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
	reviewer, _, order := addOrder(c)

	c.sessionToken = reviewer["sessionToken"].(string)
	orderId := floatToStr(order["orderId"])
	reviewRes := c.postData("reviews", url.Values{"orderId": {orderId},
		"content": {"代码写得不错！"}, "title":{"记一次动画效果"}})
	assert.NotNil(t, reviewRes["reviewId"])
	assert.Equal(t, "代码写得不错！", reviewRes["content"])
	assert.Equal(t, "记一次动画效果", reviewRes["title"])
	assert.NotNil(t, reviewRes["displaying"])
	_, has := reviewRes["coverUrl"]
	assert.Equal(t, true, has)
}

func TestReviews_Duplicate(t *testing.T) {
	c := NewClient()
	reviewer, _, order := addOrder(c)

	c.sessionToken = reviewer["sessionToken"].(string)
	orderId := floatToStr(order["orderId"])
	reviewRes := c.postData("reviews", url.Values{"orderId": {orderId},
		"content": {"代码写得不错！"}, "title":{"标题"}})
	assert.NotNil(t, reviewRes["reviewId"])

	reviewRes = c.post("reviews", url.Values{"orderId": {orderId},
		"content": {"代码写得不错！"}, "title":{"标题"}})
	assert.Equal(t, 18, toInt(reviewRes["code"]))
}

func TestReviews_EditReview(t *testing.T) {
	c := NewClient()
	reviewer, _, order := addOrder(c)

	c.sessionToken = reviewer["sessionToken"].(string)
	orderId := floatToStr(order["orderId"])
	reviewRes := c.postData("reviews", url.Values{"orderId": {orderId},
		"content": {"代码写得不错！"}, "title":{"标题"}})
	assert.NotNil(t, reviewRes["reviewId"])
	assert.Equal(t, "代码写得不错！", reviewRes["content"])

	reviewId := floatToStr(reviewRes["reviewId"])
	editRes := c.patchData("reviews/" + reviewId, url.Values{"reviewId": {reviewId},
		"content": {"这里有几个问题。"}, "title":{"新标题"}})
	assert.Equal(t, "这里有几个问题。", editRes["content"])
	assert.Equal(t, "新标题", editRes["title"])
}

func TestReviews_all(t *testing.T) {
	c := NewClient();
	_, _, _, review := addOrderAndReview(c)
	reviewId := floatToStr(review["reviewId"])
	setReviewAsDisplaying(reviewId)

	orderId := floatToStr(review["orderId"])
	reward(c, orderId)

	res,total := c.getListData("reviews", url.Values{})
	assert.Equal(t, 1, len(res));
	theReview := res[0].(map[string]interface{})
	assert.Equal(t, 1, toInt(theReview["rewardCount"]))
	assert.Equal(t, 0, toInt(theReview["visitCount"]))

	assert.Equal(t, 1, total);

	res = c.getArrayData("reviews", url.Values{"skip": {"1"}})
	assert.Equal(t, 0, len(res));
}

func TestReviews_userReviews(t *testing.T) {
	c := NewClient();
	reviewer, _, _, review := addOrderAndReview(c)
	reviewId := floatToStr(review["reviewId"])
	setReviewAsDisplaying(reviewId)

	reviewerId := reviewer["id"].(string)
	res := c.getArrayData("reviewers/" + reviewerId + "/reviews", url.Values{});
	assert.Equal(t, 1, len(res))
	theReview := res[0].(map[string]interface{})
	assert.Equal(t, 1, toInt(theReview["rewardCount"]))

	res = c.getArrayData("reviewers/" + reviewerId + "/reviews", url.Values{"limit": {"0"}});
	assert.Equal(t, 0, len(res))
}

func TestReviews_ViewByOrderId(t *testing.T) {
	c := NewClient()
	_, _, order, review := addOrderAndReview(c)
	orderId := floatToStr(order["orderId"])
	res := c.getData("orders/" + orderId + "/review", url.Values{})
	assert.Equal(t, review["reviewId"], res["reviewId"])
}
