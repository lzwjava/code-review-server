package codereview
import (
	"testing"
	"os"
	"fmt"
	"crypto/md5"
	"database/sql"
	"strconv"
	_ "reflect"
	"net/url"
)

func TestMain(m *testing.M) {
	cleanTables()
	os.Exit(m.Run())
}

func cleanTables() {
	tables := []string{"rewards", "reviews", "orders", "charges",
		"reviews_tags", "users_tags", "reviewers", "learners"}
	for _, table := range tables {
		deleteTable(table)
	}
	fmt.Println()
}

func checkErr(err error) {
	if err != nil {
		panic(err)
	}
}

func registerLearner(c *Client) map[string]interface{} {
	res := c.post("user/register", url.Values{"mobilePhoneNumber": {"1326163092"},
		"username": {"lzwjavaTest"}, "smsCode": {"5555"}, "password":{md5password("123456")}, "type": {"0"}})
	if (toInt(res["code"]) == 0) {
		registerRes := res["result"].(map[string]interface{})
		c.sessionToken = registerRes["sessionToken"].(string)
		return registerRes
	} else {
		loginRes := c.postData("user/login", url.Values{"mobilePhoneNumber": {"1326163092"},
			"password":{md5password("123456")}});
		return loginRes
	}
}

func registerReviewer(c *Client) map[string]interface{} {
	res := c.post("user/register", url.Values{"mobilePhoneNumber": {"13261630924"},
		"username": {"lzwjavaReviewer"}, "smsCode": {"5555"}, "password":{md5password("123456")}, "type": {"1"}})
	if (toInt(res["code"]) == 0) {
		registerRes := res["result"].(map[string]interface{})
		c.sessionToken = registerRes["sessionToken"].(string)
		validReviewer(c, registerRes["id"].(string))
		return registerRes
	} else {
		loginRes := c.postData("user/login", url.Values{"mobilePhoneNumber": {"13261630924"},
			"password":{md5password("123456")}});
		return loginRes
	}
}

func registerUsers(c *Client) (map[string]interface{}, map[string]interface{}) {
	reviewer := registerReviewer(c)
	learner := registerLearner(c)
	return reviewer, learner
}

func md5password(password string) string {
	data := []byte(password)
	return fmt.Sprintf("%x", md5.Sum(data))
}

func validReviewer(c *Client, reviewerId string) {
	c.getData("reviewers/" + reviewerId + "/valid", url.Values{})
}

func deleteTable(table string) {
	deleteRecord(table, "1", "1");
}

func runSql(sentence string) {
	db, err := sql.Open("mysql", "lzw:@/codereview")
	checkErr(err)

	err = db.Ping()
	checkErr(err)

	stmt, err := db.Prepare(sentence)
	checkErr(err)

	res, err := stmt.Exec()
	checkErr(err)

	affect, err := res.RowsAffected()
	checkErr(err)

	fmt.Println(sentence, "affected", affect)

	db.Close()
}

func setReviewAsDisplaying(reviewId string) {
	statement := fmt.Sprintf("update reviews set displaying=1 where reviewId=%s", reviewId);
	runSql(statement)
}

func deleteRecord(table string, column string, id string) {
	sqlStr := fmt.Sprintf("delete from %s where %s=%s", table, column, id)
	runSql(sqlStr)
}

func toInt(obj interface{}) (int) {
	if _, isFloat := obj.(float64); isFloat {
		return int(obj.(float64))
	} else {
		return obj.(int)
	}
}

func floatToStr(flt interface{}) string {
	return strconv.Itoa(toInt(flt))
}

func addOrder(c *Client) (map[string]interface{}, map[string]interface{}, map[string]interface{}) {
	cleanTables()
	reviewer, learner := registerUsers(c)

	reviewerId := reviewer["id"].(string)

	order := c.postData("orders/add", url.Values{"gitHubUrl": {"https://github.com/lzwjava/Reveal-In-GitHub"},
		"remark": {"麻烦大神了"}, "reviewerId":{reviewerId}, "codeLines":{"3000"}})
	return reviewer, learner, order
}

func addOrderAndReward(c *Client) (map[string]interface{}, map[string]interface{}, map[string]interface{}) {
	reviewer, learner, order := addOrder(c)
	reward(c, floatToStr(order["orderId"]))
	return reviewer, learner, order
}

func reward(c *Client, orderId string) {
	rewardRes := c.post("orders/" + orderId + "/reward", url.Values{"amount": {"500"}})
	orderNo := rewardRes["order_no"].(string)
	c.callWithStr("rewards/callback", testCallbackStr(orderNo, orderId, 500))
}

func addReview(c *Client, orderId string) (map[string]interface{}) {
	return c.postData("reviews", url.Values{"orderId": {orderId},
		"content": {"代码写得不错！"}, "title":{"记一次动画效果"}})
}

func addOrderAndReview(c *Client) (map[string]interface{}, map[string]interface{}, map[string]interface{},
map[string]interface{}) {
	reviewer, learner, order := addOrder(c)
	orderId := floatToStr(order["orderId"])
	c.sessionToken = reviewer["sessionToken"].(string)
	review := addReview(c, orderId)
	return reviewer, learner, order, review
}
