package codereview
import (
	"testing"
	"net/url"
	"github.com/stretchr/testify/assert"
	"time"
)

func TestVisits_count(t *testing.T) {
	c := NewClient()
	_, _, _, review := addOrderAndReview(c)

	reviewId := floatToStr(review["reviewId"])
	res := c.postData("reviews/" + reviewId + "/visits", url.Values{"referrer":{"https://google.com"}})
	assert.NotNil(t, res)

	time.Sleep(1 * time.Second)

	reviewId = floatToStr(review["reviewId"])
	res = c.postData("reviews/" + reviewId + "/visits", url.Values{"referrer":{"https://google.com"}})
	assert.NotNil(t, res)

	newClient := NewClient()
	newClient.postData("reviews/" + reviewId + "/visits", url.Values{"referrer":{"https://google.com"}})

	theReview := c.getData("reviews/" + reviewId, url.Values{})
	assert.Equal(t, 3, toInt(theReview["visitCount"]))
}

func TestVisits_empty(t *testing.T) {
	c := NewClient()
	_, _, _, review := addOrderAndReview(c)

	reviewId := floatToStr(review["reviewId"])
	res := c.postData("reviews/" + reviewId + "/visits", url.Values{"referrer":{""}})
	assert.NotNil(t, res)
}

func TestVisits_countVideo(t *testing.T) {
	c := NewClient()
	video := addVideo(c)
	videoId := floatToStr(video["videoId"])
	res := c.postData("videos/" + videoId + "/visits", url.Values{"referrer":{"https://google.com"}})
	assert.NotNil(t, res)

	theVideo := c.getData("videos/" + videoId, url.Values{})
	assert.Equal(t, 1, toInt(theVideo["visitCount"]))
}
