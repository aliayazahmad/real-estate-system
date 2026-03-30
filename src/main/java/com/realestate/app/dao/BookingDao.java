package com.realestate.app.dao;

import com.realestate.app.model.Booking;
import com.realestate.app.util.DatabaseUtil;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.Timestamp;
import java.time.LocalDate;
import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import java.util.Optional;

public class BookingDao {
    public int countAll() {
        return countBySql("SELECT COUNT(*) FROM bookings");
    }

    public int countByUser(int userId, String status) {
        String sql = status == null
            ? "SELECT COUNT(*) FROM bookings WHERE user_id = ?"
            : "SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status = ?";
        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setInt(1, userId);
            if (status != null) {
                statement.setString(2, status);
            }
            try (ResultSet resultSet = statement.executeQuery()) {
                resultSet.next();
                return resultSet.getInt(1);
            }
        } catch (Exception exception) {
            throw new RuntimeException("Unable to count bookings.", exception);
        }
    }

    public int countOpenForAgent(int agentId) {
        String sql = "SELECT COUNT(*) FROM bookings b INNER JOIN properties p ON p.id = b.property_id WHERE p.user_id = ? AND b.status IN ('pending', 'confirmed')";
        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setInt(1, agentId);
            try (ResultSet resultSet = statement.executeQuery()) {
                resultSet.next();
                return resultSet.getInt(1);
            }
        } catch (Exception exception) {
            throw new RuntimeException("Unable to count agent bookings.", exception);
        }
    }

    public List<Booking> recentForUser(int userId, int limit) {
        String sql = "SELECT b.*, p.title AS property_title, p.city AS property_city, p.location AS property_location, p.price AS property_price, p.image AS property_image, pay.id AS payment_id, pay.status AS payment_status, pay.invoice_number " +
            "FROM bookings b INNER JOIN properties p ON p.id = b.property_id LEFT JOIN payments pay ON pay.booking_id = b.id " +
            "WHERE b.user_id = ? ORDER BY b.created_at DESC, b.id DESC LIMIT ?";
        return queryList(sql, statement -> {
            statement.setInt(1, userId);
            statement.setInt(2, limit);
        });
    }

    public List<Booking> recentForAgent(int agentId, int limit) {
        String sql = "SELECT b.*, p.title AS property_title, p.city AS property_city, p.location AS property_location, u.name AS customer_name, u.email AS customer_email " +
            "FROM bookings b INNER JOIN properties p ON p.id = b.property_id INNER JOIN users u ON u.id = b.user_id " +
            "WHERE p.user_id = ? ORDER BY b.created_at DESC, b.id DESC LIMIT ?";
        return queryList(sql, statement -> {
            statement.setInt(1, agentId);
            statement.setInt(2, limit);
        });
    }

    public List<Booking> pendingForAdmin(int limit) {
        String sql = "SELECT b.*, p.title AS property_title, p.city AS property_city, p.location AS property_location, u.name AS customer_name, u.email AS customer_email " +
            "FROM bookings b INNER JOIN properties p ON p.id = b.property_id INNER JOIN users u ON u.id = b.user_id " +
            "WHERE b.status = 'pending' ORDER BY b.created_at DESC, b.id DESC LIMIT ?";
        return queryList(sql, statement -> statement.setInt(1, limit));
    }

    public boolean hasActiveRequest(int userId, int propertyId) {
        String sql = "SELECT COUNT(*) FROM bookings WHERE user_id = ? AND property_id = ? AND status IN ('pending', 'confirmed')";
        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setInt(1, userId);
            statement.setInt(2, propertyId);
            try (ResultSet resultSet = statement.executeQuery()) {
                resultSet.next();
                return resultSet.getInt(1) > 0;
            }
        } catch (Exception exception) {
            throw new RuntimeException("Unable to inspect active bookings.", exception);
        }
    }

    public int create(Booking booking) {
        String sql = "INSERT INTO bookings (user_id, property_id, booking_date, visit_date, message, status) VALUES (?, ?, ?, ?, ?, ?)";
        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql, PreparedStatement.RETURN_GENERATED_KEYS)) {
            statement.setInt(1, booking.getUserId());
            statement.setInt(2, booking.getPropertyId());
            statement.setDate(3, java.sql.Date.valueOf(booking.getBookingDate()));
            statement.setDate(4, booking.getVisitDate() == null ? null : java.sql.Date.valueOf(booking.getVisitDate()));
            statement.setString(5, booking.getMessage());
            statement.setString(6, booking.getStatus());
            statement.executeUpdate();
            try (ResultSet resultSet = statement.getGeneratedKeys()) {
                if (resultSet.next()) {
                    return resultSet.getInt(1);
                }
            }
        } catch (Exception exception) {
            throw new RuntimeException("Unable to create booking.", exception);
        }
        return 0;
    }

    public Optional<Booking> findById(int bookingId) {
        String sql = "SELECT * FROM bookings WHERE id = ? LIMIT 1";
        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setInt(1, bookingId);
            try (ResultSet resultSet = statement.executeQuery()) {
                if (resultSet.next()) {
                    return Optional.of(map(resultSet));
                }
            }
        } catch (Exception exception) {
            throw new RuntimeException("Unable to find booking.", exception);
        }
        return Optional.empty();
    }

    public Optional<Booking> findByIdForUser(int bookingId, int userId) {
        String sql = "SELECT b.*, p.title AS property_title, p.city AS property_city, p.location AS property_location, p.price AS property_price, p.image AS property_image, pay.id AS payment_id, pay.status AS payment_status, pay.invoice_number " +
            "FROM bookings b INNER JOIN properties p ON p.id = b.property_id LEFT JOIN payments pay ON pay.booking_id = b.id " +
            "WHERE b.id = ? AND b.user_id = ? LIMIT 1";
        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setInt(1, bookingId);
            statement.setInt(2, userId);
            try (ResultSet resultSet = statement.executeQuery()) {
                if (resultSet.next()) {
                    return Optional.of(map(resultSet));
                }
            }
        } catch (Exception exception) {
            throw new RuntimeException("Unable to find booking for user.", exception);
        }
        return Optional.empty();
    }

    public void updateStatus(int bookingId, String status) {
        String sql = "UPDATE bookings SET status = ? WHERE id = ?";
        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setString(1, status);
            statement.setInt(2, bookingId);
            statement.executeUpdate();
        } catch (Exception exception) {
            throw new RuntimeException("Unable to update booking status.", exception);
        }
    }

    public void cancelPendingForProperty(int propertyId, int keepBookingId) {
        String sql = "UPDATE bookings SET status = 'cancelled' WHERE property_id = ? AND id <> ? AND status = 'pending'";
        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setInt(1, propertyId);
            statement.setInt(2, keepBookingId);
            statement.executeUpdate();
        } catch (Exception exception) {
            throw new RuntimeException("Unable to cancel pending bookings.", exception);
        }
    }

    public Map<String, Integer> statusCounts() {
        Map<String, Integer> counts = new LinkedHashMap<>();
        String sql = "SELECT status, COUNT(*) AS total FROM bookings GROUP BY status ORDER BY total DESC";
        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql);
             ResultSet resultSet = statement.executeQuery()) {
            while (resultSet.next()) {
                counts.put(resultSet.getString("status"), resultSet.getInt("total"));
            }
        } catch (Exception exception) {
            throw new RuntimeException("Unable to read booking counts.", exception);
        }
        return counts;
    }

    private int countBySql(String sql) {
        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql);
             ResultSet resultSet = statement.executeQuery()) {
            resultSet.next();
            return resultSet.getInt(1);
        } catch (Exception exception) {
            throw new RuntimeException("Unable to count bookings.", exception);
        }
    }

    private List<Booking> queryList(String sql, StatementConfigurer configurer) {
        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            configurer.configure(statement);
            try (ResultSet resultSet = statement.executeQuery()) {
                List<Booking> bookings = new ArrayList<>();
                while (resultSet.next()) {
                    bookings.add(map(resultSet));
                }
                return bookings;
            }
        } catch (Exception exception) {
            throw new RuntimeException("Unable to query bookings.", exception);
        }
    }

    private Booking map(ResultSet resultSet) throws Exception {
        Booking booking = new Booking();
        booking.setId(resultSet.getInt("id"));
        booking.setUserId(resultSet.getInt("user_id"));
        booking.setPropertyId(resultSet.getInt("property_id"));
        if (resultSet.getDate("booking_date") != null) {
            booking.setBookingDate(resultSet.getDate("booking_date").toLocalDate());
        }
        if (resultSet.getDate("visit_date") != null) {
            booking.setVisitDate(resultSet.getDate("visit_date").toLocalDate());
        }
        booking.setMessage(resultSet.getString("message"));
        booking.setStatus(resultSet.getString("status"));
        safe(() -> booking.setPropertyTitle(resultSet.getString("property_title")));
        safe(() -> booking.setPropertyCity(resultSet.getString("property_city")));
        safe(() -> booking.setPropertyLocation(resultSet.getString("property_location")));
        safe(() -> booking.setPropertyPrice(resultSet.getDouble("property_price")));
        safe(() -> booking.setPropertyImage(resultSet.getString("property_image")));
        safe(() -> booking.setCustomerName(resultSet.getString("customer_name")));
        safe(() -> booking.setCustomerEmail(resultSet.getString("customer_email")));
        safe(() -> booking.setPaymentId((Integer) resultSet.getObject("payment_id")));
        safe(() -> booking.setPaymentStatus(resultSet.getString("payment_status")));
        safe(() -> booking.setInvoiceNumber(resultSet.getString("invoice_number")));
        Timestamp created = resultSet.getTimestamp("created_at");
        if (created != null) {
            booking.setCreatedAt(created.toLocalDateTime());
        }
        return booking;
    }

    private void safe(ThrowingRunnable runnable) {
        try {
            runnable.run();
        } catch (Exception ignored) {
        }
    }

    @FunctionalInterface
    private interface StatementConfigurer {
        void configure(PreparedStatement statement) throws Exception;
    }

    @FunctionalInterface
    private interface ThrowingRunnable {
        void run() throws Exception;
    }
}
