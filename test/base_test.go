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
	os.Exit(m.Run())
}

func setUp() {
	cleanTables()
}

func cleanTables() {
	deleteTable("comments", true)
	tables := []string{"notifications", "applications", "video_visits", "videos",
		"review_visits", "rewards", "reviews", "orders", "charges",
		"reviews_tags", "users_tags", "reviewers", "learners"}
	for _, table := range tables {
		deleteTable(table, false)
	}
	fmt.Println()
}

func checkErr(err error) {
	if err != nil {
		panic(err)
	}
}

func registerUser(c *Client, phone string, userType string, name string) map[string]interface{} {
	res := c.post("user/register", url.Values{"mobilePhoneNumber": {phone},
		"username": {name}, "smsCode": {"5555"}, "password":{md5password("123456")}, "type": {userType}})
	if (toInt(res["code"]) == 0) {
		registerRes := res["result"].(map[string]interface{})
		c.sessionToken = registerRes["sessionToken"].(string)
		if (userType == "reviewer") {
			validReviewer(c, registerRes["id"].(string))
		}
		return registerRes
	} else {
		loginRes := c.postData("user/login", url.Values{"mobilePhoneNumber": {phone},
			"password":{md5password("123456")}});
		return loginRes
	}
}

func registerLearner2(c *Client) map[string]interface{} {
	return registerUser(c, "18813106251", "learner", "满天星");
}

func registerLearner3(c *Client) map[string]interface{} {
	return registerUser(c, "13611456899", "learner", "月生日月");
}

func registerLearner(c *Client) map[string]interface{} {
	return registerUser(c, "18928980893", "learner", "lzwjavaTest")
}

func registerReviewer(c *Client) map[string]interface{} {
	return registerUser(c, "13261630925", "reviewer", "lzwjavaReviewer")
}

func login(c *Client, mobilePhoneNumber string, password string) map[string]interface{} {
	return c.postData("user/login", url.Values{"mobilePhoneNumber": {mobilePhoneNumber},
		"password":{md5password(password)}});
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

func setReviewAsDisplaying(reviewId string) {
	statement := fmt.Sprintf("update reviews set displaying=1 where reviewId=%s", reviewId);
	runSql(statement, false)
}

func deleteTable(table string, noCheck bool) {
	deleteRecord(table, "1", "1", noCheck);
}

func runSql(sentence string, noCheck bool) {
	db, err := sql.Open("mysql", "lzw:@/codereview")
	checkErr(err)

	err = db.Ping()
	checkErr(err)

	var stmt *sql.Stmt
	var res sql.Result

	if noCheck {
		stmt, err = db.Prepare("SET FOREIGN_KEY_CHECKS=0")
		checkErr(err)

		res, err = stmt.Exec()
		checkErr(err)
	}


	stmt, err = db.Prepare(sentence)
	checkErr(err)

	res, err = stmt.Exec()
	checkErr(err)

	affect, err := res.RowsAffected()
	checkErr(err)

	fmt.Println(sentence, "affected", affect)

	if noCheck {
		stmt, err = db.Prepare("SET FOREIGN_KEY_CHECKS=1")
		checkErr(err)

		res, err = stmt.Exec()
		checkErr(err)
	}

	db.Close()
}

func deleteRecord(table string, column string, id string, noCheck bool) {
	sqlStr := fmt.Sprintf("delete from %s where %s=%s", table, column, id)
	runSql(sqlStr, noCheck)
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
	reviewer, learner := registerUsers(c)

	reviewerId := reviewer["id"].(string)

	order := c.postData("orders/add", url.Values{"gitHubUrl": {"https://github.com/lzwjava/Reveal-In-GitHub"},
		"remark": {"麻烦大神了"}, "reviewerId":{reviewerId}, "codeLines":{"3000"}, "amount":{"5000"}})
	return reviewer, learner, order
}

func addOrderAndReward(c *Client) (map[string]interface{}, map[string]interface{}, map[string]interface{}) {
	reviewer, learner, order := addOrder(c)
	reward(c, floatToStr(order["orderId"]))
	return reviewer, learner, order
}

func reward(c *Client, orderId string) {
	rewardRes := c.post("orders/" + orderId + "/reward", url.Values{})
	orderNo := rewardRes["order_no"].(string)
	c.postWithStr("rewards/callback", testCallbackStr(orderNo, orderId, 5000))
}

func rewardAmount(c *Client, orderId string, amount int) map[string]interface{} {
	rewardRes := c.post("orders/" + orderId + "/reward", url.Values{"amount": {floatToStr(amount)}})
	orderNo := rewardRes["order_no"].(string)
	return c.postWithStr("rewards/callback", testCallbackStr(orderNo, orderId, amount))
}

func addReview(c *Client, orderId string) (map[string]interface{}) {
	return c.postData("reviews", url.Values{"orderId": {orderId},
		"content": {"代码写得不错！"}, "title":{"记一次动画效果"}})
}

func addOrderAndReview(c *Client) (map[string]interface{}, map[string]interface{}, map[string]interface{},
map[string]interface{}) {
	reviewer, learner, order := addOrder(c)
	orderId := floatToStr(order["orderId"])
	reward(c, orderId)
	c.sessionToken = reviewer["sessionToken"].(string)
	review := addReview(c, orderId)
	return reviewer, learner, order, review
}
