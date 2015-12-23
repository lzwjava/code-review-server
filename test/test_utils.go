package codereview

import (
	"fmt"
	_ "reflect"
	"net/url"
	"database/sql"
	"strconv"
)

func checkErr(err error) {
	if err != nil {
		panic(err)
	}
}


func registerLearner(c *Client) map[string]interface{} {
	res := c.callData("user/register", url.Values{"mobilePhoneNumber": {"1326163092"},
		"username": {"lzwjavaTest"}, "smsCode": {"5555"}, "password":{"123456"}, "type": {"0"}})
	return res
}

func registerReviewer(c *Client) map[string]interface{} {
	res := c.callData("user/register", url.Values{"mobilePhoneNumber": {"13261630924"},
		"username": {"lzwjavaReviewer"}, "smsCode": {"5555"}, "password":{"123456"}, "type": {"1"}})
	validReviewer(c, res["id"].(string))
	return res
}

func registerUsers(c *Client) (map[string]interface{}, map[string]interface{} ){
    reviewer := registerReviewer(c)
	learner := registerLearner(c)
	return reviewer, learner
}

func validReviewer(c *Client, reviewerId string) {
	c.getData("reviewers/valid", url.Values{"id": {reviewerId}})
}

func deleteTable(table string) {
	deleteRecord(table, "1", "1");
}

func deleteRecord(table string, column string, id string) {
	db, err := sql.Open("mysql", "lzw:@/codereview")
	checkErr(err)

	err = db.Ping()
	checkErr(err)

	sql := fmt.Sprintf("delete from %s where %s=?", table, column)

	stmt, err := db.Prepare(sql)
	checkErr(err)

	res, err := stmt.Exec(id)
	checkErr(err)

	affect, err := res.RowsAffected()
	checkErr(err)

	fmt.Println(sql, "affected", affect)

	db.Close()
}

func toInt(obj interface{}) (int) {
	return int(obj.(float64))
}

func floatToStr(flt interface{}) string {
	return strconv.Itoa(toInt(flt))
}
