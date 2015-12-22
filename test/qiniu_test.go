package codereview
import (
	"testing"
	"net/url"
	"github.com/stretchr/testify/assert"
)

func TestToken(t *testing.T) {
	c := NewClient()
	res := c.getData("qiniu/token", url.Values{})
	assert.NotNil(t, res["token"])
	assert.Equal(t, "http://7xotd0.com1.z0.glb.clouddn.com", res["bucketUrl"].(string))
}
