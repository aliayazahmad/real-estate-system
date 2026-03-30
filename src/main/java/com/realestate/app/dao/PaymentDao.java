package com.realestate.app.dao;

import com.realestate.app.model.Payment;
import com.realestate.app.util.DatabaseUtil;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.Timestamp;
import java.util.ArrayList;
import java.util.List;
import java.util.Optional;

public class PaymentDao {
    public int countPaid() {
        String sql = "SELECT COUNT(*) FROM payments WHERE status = 'paid'";
        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql);
             ResultSet resultSet = statement.executeQuery()) {
            resultSet.next();
            return resultSet.getInt(1);
        } catch (Exception exception) {
            throw new RuntimeException("Unable to count paid payments.", exception);
        }
    }

    public int countPaidByUser(int userId) {
        String sql = "SELECT COUNT(*) FROM payments pay INNER JOIN bookings b ON b.id = pay.booking_id WHERE pay.status = 'paid' AND b.user_id = ?";
        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setInt(1, userId);
            try (ResultSet resultSet = statement.executeQuery()) {
                resultSet.next();
                return resultSet.getInt(1);
            }
        } catch (Exception exception) {
            throw new RuntimeException("Unable to count user payments.", exception);
        }
    }

    public double totalRevenue() {
        String sql = "SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'paid'";
        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql);
             ResultSet resultSet = statement.executeQuery()) {
            resultSet.next();
            return resultSet.getDouble(1);
        } catch (Exception exception) {
            throw new RuntimeException("Unable to total revenue.", exception);
        }
    }

    public List<Payment> recent(int limit) {
        String sql = "SELECT pay.*, u.name AS customer_name, u.email AS customer_email, p.title AS property_title, p.city AS property_city, p.location AS property_location, b.visit_date " +
            "FROM payments pay INNER JOIN bookings b ON b.id = pay.booking_id INNER JOIN users u ON u.id = b.user_id INNER JOIN properties p ON p.id = b.property_id " +
            "ORDER BY pay.payment_date DESC, pay.id DESC LIMIT ?";
        List<Payment> payments = new ArrayList<>();
        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setInt(1, limit);
            try (ResultSet resultSet = statement.executeQuery()) {
                while (resultSet.next()) {
                    payments.add(map(resultSet));
                }
            }
        } catch (Exception exception) {
            throw new RuntimeException("Unable to query payments.", exception);
        }
        return payments;
    }

    public List<Payment> listByUser(int userId, int limit) {
        String sql = "SELECT pay.*, b.user_id, u.name AS customer_name, u.email AS customer_email, p.title AS property_title, p.city AS property_city, p.location AS property_location, b.visit_date " +
            "FROM payments pay INNER JOIN bookings b ON b.id = pay.booking_id INNER JOIN users u ON u.id = b.user_id INNER JOIN properties p ON p.id = b.property_id " +
            "WHERE b.user_id = ? ORDER BY pay.payment_date DESC, pay.id DESC LIMIT ?";
        List<Payment> payments = new ArrayList<>();
        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setInt(1, userId);
            statement.setInt(2, limit);
            try (ResultSet resultSet = statement.executeQuery()) {
                while (resultSet.next()) {
                    payments.add(map(resultSet));
                }
            }
        } catch (Exception exception) {
            throw new RuntimeException("Unable to query user payments.", exception);
        }
        return payments;
    }

    public Optional<Payment> findByBookingId(int bookingId) {
        String sql = "SELECT * FROM payments WHERE booking_id = ? LIMIT 1";
        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setInt(1, bookingId);
            try (ResultSet resultSet = statement.executeQuery()) {
                if (resultSet.next()) {
                    return Optional.of(map(resultSet));
                }
            }
        } catch (Exception exception) {
            throw new RuntimeException("Unable to find payment by booking id.", exception);
        }
        return Optional.empty();
    }

    public Optional<Payment> findReceipt(int paymentId) {
        String sql = "SELECT pay.*, u.name AS customer_name, u.email AS customer_email, p.title AS property_title, p.city AS property_city, p.location AS property_location, b.visit_date, b.user_id " +
            "FROM payments pay INNER JOIN bookings b ON b.id = pay.booking_id INNER JOIN users u ON u.id = b.user_id INNER JOIN properties p ON p.id = b.property_id WHERE pay.id = ? LIMIT 1";
        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setInt(1, paymentId);
            try (ResultSet resultSet = statement.executeQuery()) {
                if (resultSet.next()) {
                    Payment payment = map(resultSet);
                    return Optional.of(payment);
                }
            }
        } catch (Exception exception) {
            throw new RuntimeException("Unable to read payment receipt.", exception);
        }
        return Optional.empty();
    }

    public int create(Payment payment) {
        String sql = "INSERT INTO payments (booking_id, amount, payment_method, transaction_ref, payment_date, status, invoice_number, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql, PreparedStatement.RETURN_GENERATED_KEYS)) {
            statement.setInt(1, payment.getBookingId());
            statement.setDouble(2, payment.getAmount());
            statement.setString(3, payment.getPaymentMethod());
            statement.setString(4, payment.getTransactionRef());
            statement.setTimestamp(5, Timestamp.valueOf(payment.getPaymentDate()));
            statement.setString(6, payment.getStatus());
            statement.setString(7, payment.getInvoiceNumber());
            statement.setString(8, payment.getNotes());
            statement.executeUpdate();
            try (ResultSet resultSet = statement.getGeneratedKeys()) {
                if (resultSet.next()) {
                    return resultSet.getInt(1);
                }
            }
        } catch (Exception exception) {
            throw new RuntimeException("Unable to create payment.", exception);
        }
        return 0;
    }

    private Payment map(ResultSet resultSet) throws Exception {
        Payment payment = new Payment();
        payment.setId(resultSet.getInt("id"));
        payment.setBookingId(resultSet.getInt("booking_id"));
        safe(() -> payment.setUserId(resultSet.getInt("user_id")));
        payment.setAmount(resultSet.getDouble("amount"));
        payment.setPaymentMethod(resultSet.getString("payment_method"));
        payment.setTransactionRef(resultSet.getString("transaction_ref"));
        Timestamp paymentTimestamp = resultSet.getTimestamp("payment_date");
        if (paymentTimestamp != null) {
            payment.setPaymentDate(paymentTimestamp.toLocalDateTime());
        }
        payment.setStatus(resultSet.getString("status"));
        payment.setInvoiceNumber(resultSet.getString("invoice_number"));
        payment.setNotes(resultSet.getString("notes"));
        safe(() -> payment.setCustomerName(resultSet.getString("customer_name")));
        safe(() -> payment.setCustomerEmail(resultSet.getString("customer_email")));
        safe(() -> payment.setPropertyTitle(resultSet.getString("property_title")));
        safe(() -> payment.setPropertyCity(resultSet.getString("property_city")));
        safe(() -> payment.setPropertyLocation(resultSet.getString("property_location")));
        safe(() -> payment.setVisitDate(resultSet.getDate("visit_date").toLocalDate()));
        return payment;
    }

    private void safe(ThrowingRunnable runnable) {
        try {
            runnable.run();
        } catch (Exception ignored) {
        }
    }

    @FunctionalInterface
    private interface ThrowingRunnable {
        void run() throws Exception;
    }
}
