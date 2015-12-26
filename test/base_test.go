package codereview
import (
	"testing"
	"os"
	"fmt"
)

func TestMain(m *testing.M) {
	cleanTables()
	os.Exit(m.Run())
}

func cleanTables() {
	tables := []string{"rewards", "reviews", "orders", "charges",
		"orders_tags", "users_tags", "reviewers", "learners"}
	for _, table := range tables {
		deleteTable(table)
	}
	fmt.Println()
}
