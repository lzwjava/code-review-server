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
