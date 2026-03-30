package com.realestate.app.util;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

public final class DatabaseUtil {
    private static final String DEFAULT_HOST = "127.0.0.1";
    private static final String DEFAULT_PORT = "3306";
    private static final String DEFAULT_NAME = "real_estate_board";
    private static final String DEFAULT_USER = "root";
    private static final String DEFAULT_PASSWORD = "";

    static {
        try {
            Class.forName("com.mysql.cj.jdbc.Driver");
        } catch (ClassNotFoundException exception) {
            throw new RuntimeException("MySQL JDBC driver not found. Add mysql-connector-j to the classpath.", exception);
        }
    }

    private DatabaseUtil() {
    }

    public static Connection getConnection() throws SQLException {
        String host = read("REAL_ESTATE_DB_HOST", DEFAULT_HOST);
        String port = read("REAL_ESTATE_DB_PORT", DEFAULT_PORT);
        String name = read("REAL_ESTATE_DB_NAME", DEFAULT_NAME);
        String user = read("REAL_ESTATE_DB_USER", DEFAULT_USER);
        String password = read("REAL_ESTATE_DB_PASS", DEFAULT_PASSWORD);
        String url = "jdbc:mysql://" + host + ":" + port + "/" + name + "?useSSL=false&allowPublicKeyRetrieval=true&serverTimezone=UTC";
        return DriverManager.getConnection(url, user, password);
    }

    private static String read(String key, String fallback) {
        String value = System.getenv(key);
        if (value == null || value.trim().isEmpty()) {
            value = System.getProperty(key, fallback);
        }
        return value;
    }
}
