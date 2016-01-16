package codereview

import (
	"testing"
	"net/url"
	"github.com/stretchr/testify/assert"
)

func TestVideos_add(t *testing.T) {
	c := NewClient()
	video := c.postData("videos", url.Values{"title": {"＃05 Autolayout 和 Mansonry"},
		"source":{"http://player.youku.com/player.php/Type/Folder/Fid/26542890/Ob/1/sid/XMTQ0NjY0OTE0NA==/v.swf"}});
	assert.NotNil(t, video["videoId"])
	assert.Equal(t, video["title"], "＃05 Autolayout 和 Mansonry");
	assert.NotNil(t, video["source"])
	assert.NotNil(t, video["created"])
	assert.NotNil(t, video["updated"])
	assert.NotNil(t, video["visitCount"])
}

func TestVideos_getOne(t *testing.T) {
	c := NewClient()
	video := addVideo(c)
	videoId := floatToStr(video["videoId"])
	theVideo := c.getData("videos/" + videoId, url.Values{})
	assert.Equal(t, video["title"], theVideo["title"])
}

func addVideo(c *Client) map[string]interface{} {
	video := c.postData("videos", url.Values{"title": {"＃05 Autolayout 和 Mansonry"},
		"source":{"http://player.youku.com/player.php/Type/Folder/Fid/26542890/Ob/1/sid/XMTQ0NjY0OTE0NA==/v.swf"}});
	return video
}

func TestVideos_list(t *testing.T) {
	TestVideos_add(t)

	c := NewClient()
	videos := c.getArrayData("videos", url.Values{})
	assert.Equal(t, 1, len(videos))
}
