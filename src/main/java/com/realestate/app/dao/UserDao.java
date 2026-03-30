package com.realestate.app.dao;

import com.realestate.app.model.SessionUser;
import com.realestate.app.model.User;
import com.realestate.app.util.DatabaseUtil;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.Timestamp;
import java.util.Optional;

public class UserDao {
    public Optional<User> findByEmail(String email) {
        String sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setString(1, email);
            try (ResultSet resultSet = statement.executeQuery()) {
                if (resultSet.next()) {
                    return Optional.of(map(resultSet));
                }
            }
        } catch (Exception exception) {
            throw new RuntimeException("Unable to find user by email.", exception);
        }
        return Optional.empty();
    }

    public Optional<User> findById(int id) {
        String sql = "SELECT * FROM users WHERE id = ? LIMIT 1";
        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setInt(1, id);
            try (ResultSet resultSet = statement.executeQuery()) {
                if (resultSet.next()) {
                    return Optional.of(map(resultSet));
                }
            }
        } catch (Exception exception) {
            throw new RuntimeException("Unable to find user by id.", exception);
        }
        return Optional.empty();
    }

    public int create(User user) {
        String sql = "INSERT INTO users (name, email, phone, password_hash, role) VALUES (?, ?, ?, ?, ?)";
        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql, PreparedStatement.RETURN_GENERATED_KEYS)) {
            statement.setString(1, user.getName());
            statement.setString(2, user.getEmail());
            statement.setString(3, user.getPhone());
            statement.setString(4, user.getPasswordHash());
            statement.setString(5, user.getRole());
            statement.executeUpdate();
            try (ResultSet resultSet = statement.getGeneratedKeys()) {
                if (resultSet.next()) {
                    return resultSet.getInt(1);
                }
            }
        } catch (Exception exception) {
            throw new RuntimeException("Unable to create user.", exception);
        }
        return 0;
    }

    public void updateProfile(User user, boolean updatePassword) {
        String sql = updatePassword
            ? "UPDATE users SET name = ?, phone = ?, password_hash = ? WHERE id = ?"
            : "UPDATE users SET name = ?, phone = ? WHERE id = ?";

        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setString(1, user.getName());
            statement.setString(2, user.getPhone());
            if (updatePassword) {
                statement.setString(3, user.getPasswordHash());
                statement.setInt(4, user.getId());
            } else {
                statement.setInt(3, user.getId());
            }
            statement.executeUpdate();
        } catch (Exception exception) {
            throw new RuntimeException("Unable to update profile.", exception);
        }
    }

    public int countAll() {
        String sql = "SELECT COUNT(*) FROM users";
        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql);
             ResultSet resultSet = statement.executeQuery()) {
            resultSet.next();
            return resultSet.getInt(1);
        } catch (Exception exception) {
            throw new RuntimeException("Unable to count users.", exception);
        }
    }

    public SessionUser toSessionUser(User user) {
        SessionUser sessionUser = new SessionUser();
        sessionUser.setId(user.getId());
        sessionUser.setName(user.getName());
        sessionUser.setEmail(user.getEmail());
        sessionUser.setRole(user.getRole());
        return sessionUser;
    }

    private User map(ResultSet resultSet) throws Exception {
        User user = new User();
        user.setId(resultSet.getInt("id"));
        user.setName(resultSet.getString("name"));
        user.setEmail(resultSet.getString("email"));
        user.setPhone(resultSet.getString("phone"));
        user.setPasswordHash(resultSet.getString("password_hash"));
        user.setRole(resultSet.getString("role"));
        Timestamp timestamp = resultSet.getTimestamp("created_at");
        if (timestamp != null) {
            user.setCreatedAt(timestamp.toLocalDateTime());
        }
        return user;
    }
}
