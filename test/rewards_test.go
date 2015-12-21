package codereview

import (
	"testing"
	_"database/sql"
	_ "github.com/go-sql-driver/mysql"
	_ "github.com/stretchr/testify/assert"
	_ "fmt"
	"github.com/stretchr/testify/assert"
	"net/url"
)


func TestOrders_Reward(t *testing.T) {
	c := NewClient()
	_, learner, order := addOrder(c, t)

	c.sessionToken = learner["sessionToken"].(string)
	orderId := floatToStr(order["orderId"])

	rewardRes, err := c.call("orders/reward", url.Values{"orderId": {orderId},
		"amount": {"1000"}})
	checkErr(err)
	assert.NotNil(t, rewardRes)
	assert.Equal(t, 16, toInt(rewardRes["resultCode"]));
	assert.Equal(t, "申请者打赏金额至少为 5 元", rewardRes["resultInfo"].(string));

	rewardRes, err = c.call("orders/reward", url.Values{"orderId": {orderId},
		"amount": {"5000"}})
	checkErr(err);

	orderNo := rewardRes["order_no"].(string)
	c.callWithStr("rewards/callback", testCallbackStr(orderNo))
}