package codereview

import (
	"testing"
	"net/url"
	"github.com/stretchr/testify/assert"
)

func TestComments_create(t *testing.T) {
	setUp()

	c := NewClient()
	_, _, _, review := addOrderAndReview(c)
	reviewId := floatToStr(review["reviewId"])
	res := c.postData("reviews/" + reviewId + "/comments", url.Values{"content": {"大惊小怪"}})
	assert.NotNil(t, res)
	assert.NotNil(t, res["commentId"])

	commentId := floatToStr(res["commentId"])
	res = c.postData("reviews/" + reviewId + "/comments",
		url.Values{"content": {"呵呵"}, "parentId":{commentId}})
	assert.NotNil(t, res)
	assert.NotNil(t, res["commentId"])
}

func addComment(c *Client, reviewId string) string {
	res := c.postData("reviews/" + reviewId + "/comments", url.Values{"content": {"大惊小怪"}})
	commentId := floatToStr(res["commentId"])
	return commentId
}

func TestComments_count(t *testing.T) {
	setUp()
	c := NewClient()
	_, _, _, review, _ := addReviewAndComment(c)
	reviewId := floatToStr(review["reviewId"])
	post := c.getData("reviews/" + reviewId, url.Values{})
	assert.Equal(t, toInt(post["commentCount"]), 1)
}

func addReviewAndComment(c *Client) (map[string]interface{}, map[string]interface{},
map[string]interface{}, map[string]interface{}, string) {
	reviewer, learner, order, review := addOrderAndReview(c)
	reviewId := floatToStr(review["reviewId"])
	commentId := addComment(c, reviewId)
	return reviewer, learner, order, review, commentId
}

func TestComments_list(t *testing.T) {
	setUp()

	c := NewClient()
	_, _, _, review, _ := addReviewAndComment(c)
	reviewId := floatToStr(review["reviewId"])
	res := c.getArrayData("reviews/" + reviewId + "/comments", url.Values{})
	assert.NotNil(t, res)
	assert.Equal(t, len(res), 1)
	comment := res[0].(map[string]interface{})
	assert.NotNil(t, comment["author"])
	author := comment["author"].(map[string]interface{})
	assert.NotNil(t, author["id"])
	assert.NotNil(t, author["username"])
	assert.NotNil(t, comment["commentId"])
	assert.NotNil(t, comment["reviewId"])
	assert.NotNil(t, comment["created"])
}

func getCommentId(comment interface{}) string {
	return floatToStr(comment.(map[string]interface{})["commentId"])
}
