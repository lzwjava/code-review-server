package codereview
import (
	"testing"
	"os"
	"fmt"
)

func TestMain(m *testing.M) {
	tables := []string{"reviewers", "learners", "orders", "rewards", "charges", "reviews"}
	for _, table := range tables {
		deleteTable(table)
	}
	fmt.Println()
	os.Exit(m.Run())
}
