package codereview
import (
	"testing"
	"net/url"
	"github.com/stretchr/testify/assert"
)

func TestReviewers_Get(t *testing.T) {
	c := NewClient()
	registerReviewer(c)
	res := c.getArrayData("reviewers", url.Values{})
	assert.Equal(t, 1, len(res))
	reviewer := res[0].(map[string]interface{})
	assert.Equal(t, 0, len(reviewer["tags"].([]interface{})))
}
