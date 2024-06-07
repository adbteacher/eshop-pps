<?php
use PHPUnit\Framework\TestCase;

require_once './8rol_admin/crear_usuario.php';

class CrearUsuarioTest extends TestCase {

    public function testValidarSQLDetectsSQLKeywords() {
        $sqlKeywords = [
            "SELECT * FROM users",
            "DROP TABLE students",
            "INSERT INTO users (name, email) VALUES ('John', 'john@example.com')",
            "UPDATE users SET name='Jane' WHERE id=1",
            "DELETE FROM users WHERE id=1",
            "UNION SELECT password FROM users",
            "CREATE TABLE test (id INT)",
            "ALTER TABLE test ADD COLUMN name VARCHAR(50)",
            "TRUNCATE TABLE test"
        ];

        foreach ($sqlKeywords as $keyword) {
            $this->assertTrue(validarSQL($keyword), "Failed to detect SQL keyword in: $keyword");
        }
    }

    public function testValidarSQLIgnoresNonSQLStrings() {
        $nonSqlStrings = [
            "Hello world",
            "This is a test string",
            "Another example",
            "Just some random text",
            "safe_string_without_sql_keywords"
        ];

        foreach ($nonSqlStrings as $string) {
            $this->assertFalse(validarSQL($string), "Incorrectly detected SQL keyword in: $string");
        }
    }

    public function testValidarSQLHandlesEmptyString() {
        $this->assertFalse(validarSQL(""), "Failed to handle empty string");
    }

    public function testValidarSQLCaseInsensitiveDetection() {
        $mixedCaseSQL = [
            "select * from users",
            "dRoP table students",
            "Insert Into users (name, email) VALUES ('John', 'john@example.com')"
        ];

        foreach ($mixedCaseSQL as $keyword) {
            $this->assertTrue(validarSQL($keyword), "Failed to detect SQL keyword in mixed case: $keyword");
        }
    }
}
?>
