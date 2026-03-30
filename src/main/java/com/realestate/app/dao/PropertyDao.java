package com.realestate.app.dao;

import com.realestate.app.model.Property;
import com.realestate.app.model.SessionUser;
import com.realestate.app.util.DatabaseUtil;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.Timestamp;
import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import java.util.Optional;

public class PropertyDao {
    public int countApproved() {
        return countBySql("SELECT COUNT(*) FROM properties WHERE status = 'approved'");
    }

    public int countAll() {
        return countBySql("SELECT COUNT(*) FROM properties");
    }

    public int countByOwner(int ownerId, String status) {
        String sql = status == null
            ? "SELECT COUNT(*) FROM properties WHERE user_id = ?"
            : "SELECT COUNT(*) FROM properties WHERE user_id = ? AND status = ?";

        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setInt(1, ownerId);
            if (status != null) {
                statement.setString(2, status);
            }
            try (ResultSet resultSet = statement.executeQuery()) {
                resultSet.next();
                return resultSet.getInt(1);
            }
        } catch (Exception exception) {
            throw new RuntimeException("Unable to count owner properties.", exception);
        }
    }

    public List<Property> latestApproved(int limit) {
        String sql = "SELECT p.*, u.name AS owner_name, u.email AS owner_email FROM properties p " +
            "LEFT JOIN users u ON u.id = p.user_id WHERE p.status = 'approved' ORDER BY p.created_at DESC, p.id DESC LIMIT ?";
        return queryList(sql, statement -> statement.setInt(1, limit));
    }

    public List<Property> listByOwner(int ownerId, int limit) {
        String sql = "SELECT p.*, u.name AS owner_name, u.email AS owner_email FROM properties p " +
            "LEFT JOIN users u ON u.id = p.user_id WHERE p.user_id = ? ORDER BY p.created_at DESC, p.id DESC LIMIT ?";
        return queryList(sql, statement -> {
            statement.setInt(1, ownerId);
            statement.setInt(2, limit);
        });
    }

    public List<Property> pendingApproval(int limit) {
        String sql = "SELECT p.*, u.name AS owner_name, u.email AS owner_email FROM properties p " +
            "LEFT JOIN users u ON u.id = p.user_id WHERE p.status = 'pending' ORDER BY p.created_at DESC, p.id DESC LIMIT ?";
        return queryList(sql, statement -> statement.setInt(1, limit));
    }

    public List<Property> search(Map<String, String> filters, SessionUser currentUser) {
        StringBuilder sql = new StringBuilder("SELECT p.*, u.name AS owner_name, u.email AS owner_email FROM properties p LEFT JOIN users u ON u.id = p.user_id WHERE 1 = 1");
        List<Object> params = new ArrayList<>();

        if (currentUser == null) {
            sql.append(" AND p.status = 'approved'");
        } else if ("admin".equalsIgnoreCase(currentUser.getRole())) {
            if (hasValue(filters.get("status"))) {
                sql.append(" AND p.status = ?");
                params.add(filters.get("status"));
            }
        } else if ("agent".equalsIgnoreCase(currentUser.getRole())) {
            sql.append(" AND (p.status = 'approved' OR p.user_id = ?)");
            params.add(currentUser.getId());
            if (hasValue(filters.get("status"))) {
                sql.append(" AND p.status = ?");
                params.add(filters.get("status"));
            }
        } else {
            sql.append(" AND p.status = 'approved'");
        }

        if (hasValue(filters.get("q"))) {
            sql.append(" AND (p.title LIKE ? OR p.city LIKE ? OR p.location LIKE ? OR p.description LIKE ?)");
            String query = "%" + filters.get("q").trim() + "%";
            params.add(query);
            params.add(query);
            params.add(query);
            params.add(query);
        }
        if (hasValue(filters.get("city"))) {
            sql.append(" AND p.city = ?");
            params.add(filters.get("city"));
        }
        if (hasValue(filters.get("property_type"))) {
            sql.append(" AND p.property_type = ?");
            params.add(filters.get("property_type"));
        }
        if (hasValue(filters.get("purpose"))) {
            sql.append(" AND p.purpose = ?");
            params.add(filters.get("purpose"));
        }
        if (hasValue(filters.get("min_price"))) {
            sql.append(" AND p.price >= ?");
            params.add(Double.parseDouble(filters.get("min_price")));
        }
        if (hasValue(filters.get("max_price"))) {
            sql.append(" AND p.price <= ?");
            params.add(Double.parseDouble(filters.get("max_price")));
        }

        sql.append(" ORDER BY CASE p.status WHEN 'pending' THEN 1 WHEN 'approved' THEN 2 WHEN 'booked' THEN 3 WHEN 'rejected' THEN 4 ELSE 5 END, p.created_at DESC, p.id DESC");

        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql.toString())) {
            bind(statement, params);
            try (ResultSet resultSet = statement.executeQuery()) {
                List<Property> properties = new ArrayList<>();
                while (resultSet.next()) {
                    properties.add(map(resultSet));
                }
                return properties;
            }
        } catch (Exception exception) {
            throw new RuntimeException("Unable to search properties.", exception);
        }
    }

    public Optional<Property> findById(int id) {
        String sql = "SELECT p.*, u.name AS owner_name, u.email AS owner_email FROM properties p LEFT JOIN users u ON u.id = p.user_id WHERE p.id = ? LIMIT 1";
        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setInt(1, id);
            try (ResultSet resultSet = statement.executeQuery()) {
                if (resultSet.next()) {
                    return Optional.of(map(resultSet));
                }
            }
        } catch (Exception exception) {
            throw new RuntimeException("Unable to load property.", exception);
        }
        return Optional.empty();
    }

    public boolean existsByTitleAndLocation(String title, String location, Integer excludeId) {
        String sql = excludeId == null
            ? "SELECT COUNT(*) FROM properties WHERE LOWER(TRIM(title)) = LOWER(TRIM(?)) AND LOWER(TRIM(location)) = LOWER(TRIM(?))"
            : "SELECT COUNT(*) FROM properties WHERE id <> ? AND LOWER(TRIM(title)) = LOWER(TRIM(?)) AND LOWER(TRIM(location)) = LOWER(TRIM(?))";
        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            if (excludeId == null) {
                statement.setString(1, title);
                statement.setString(2, location);
            } else {
                statement.setInt(1, excludeId);
                statement.setString(2, title);
                statement.setString(3, location);
            }
            try (ResultSet resultSet = statement.executeQuery()) {
                resultSet.next();
                return resultSet.getInt(1) > 0;
            }
        } catch (Exception exception) {
            throw new RuntimeException("Unable to check property duplicates.", exception);
        }
    }

    public int create(Property property) {
        String sql = "INSERT INTO properties (user_id, title, city, location, price, property_type, purpose, bedrooms, bathrooms, area_sqft, description, image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql, PreparedStatement.RETURN_GENERATED_KEYS)) {
            statement.setInt(1, property.getUserId());
            statement.setString(2, property.getTitle());
            statement.setString(3, property.getCity());
            statement.setString(4, property.getLocation());
            statement.setDouble(5, property.getPrice());
            statement.setString(6, property.getPropertyType());
            statement.setString(7, property.getPurpose());
            setNullableInteger(statement, 8, property.getBedrooms());
            setNullableInteger(statement, 9, property.getBathrooms());
            setNullableInteger(statement, 10, property.getAreaSqft());
            statement.setString(11, property.getDescription());
            statement.setString(12, property.getImage());
            statement.setString(13, property.getStatus());
            statement.executeUpdate();
            try (ResultSet resultSet = statement.getGeneratedKeys()) {
                if (resultSet.next()) {
                    return resultSet.getInt(1);
                }
            }
        } catch (Exception exception) {
            throw new RuntimeException("Unable to create property.", exception);
        }
        return 0;
    }

    public void update(Property property) {
        String sql = "UPDATE properties SET title = ?, city = ?, location = ?, price = ?, property_type = ?, purpose = ?, bedrooms = ?, bathrooms = ?, area_sqft = ?, description = ?, image = ?, status = ? WHERE id = ?";
        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setString(1, property.getTitle());
            statement.setString(2, property.getCity());
            statement.setString(3, property.getLocation());
            statement.setDouble(4, property.getPrice());
            statement.setString(5, property.getPropertyType());
            statement.setString(6, property.getPurpose());
            setNullableInteger(statement, 7, property.getBedrooms());
            setNullableInteger(statement, 8, property.getBathrooms());
            setNullableInteger(statement, 9, property.getAreaSqft());
            statement.setString(10, property.getDescription());
            statement.setString(11, property.getImage());
            statement.setString(12, property.getStatus());
            statement.setInt(13, property.getId());
            statement.executeUpdate();
        } catch (Exception exception) {
            throw new RuntimeException("Unable to update property.", exception);
        }
    }

    public void updateStatus(int propertyId, String status) {
        String sql = "UPDATE properties SET status = ? WHERE id = ?";
        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setString(1, status);
            statement.setInt(2, propertyId);
            statement.executeUpdate();
        } catch (Exception exception) {
            throw new RuntimeException("Unable to update property status.", exception);
        }
    }

    public void delete(int propertyId) {
        String sql = "DELETE FROM properties WHERE id = ?";
        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setInt(1, propertyId);
            statement.executeUpdate();
        } catch (Exception exception) {
            throw new RuntimeException("Unable to delete property.", exception);
        }
    }

    public boolean hasBookings(int propertyId) {
        String sql = "SELECT COUNT(*) FROM bookings WHERE property_id = ?";
        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setInt(1, propertyId);
            try (ResultSet resultSet = statement.executeQuery()) {
                resultSet.next();
                return resultSet.getInt(1) > 0;
            }
        } catch (Exception exception) {
            throw new RuntimeException("Unable to inspect property bookings.", exception);
        }
    }

    public Map<String, Integer> statusCounts() {
        Map<String, Integer> counts = new LinkedHashMap<>();
        String sql = "SELECT status, COUNT(*) AS total FROM properties GROUP BY status ORDER BY total DESC";
        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql);
             ResultSet resultSet = statement.executeQuery()) {
            while (resultSet.next()) {
                counts.put(resultSet.getString("status"), resultSet.getInt("total"));
            }
        } catch (Exception exception) {
            throw new RuntimeException("Unable to read property status counts.", exception);
        }
        return counts;
    }

    public List<String[]> topCities(int limit) {
        String sql = "SELECT city, COUNT(*) AS total FROM properties WHERE city IS NOT NULL AND city <> '' GROUP BY city ORDER BY total DESC, city ASC LIMIT ?";
        List<String[]> rows = new ArrayList<>();
        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setInt(1, limit);
            try (ResultSet resultSet = statement.executeQuery()) {
                while (resultSet.next()) {
                    rows.add(new String[]{resultSet.getString("city"), String.valueOf(resultSet.getInt("total"))});
                }
            }
        } catch (Exception exception) {
            throw new RuntimeException("Unable to read top cities.", exception);
        }
        return rows;
    }

    private int countBySql(String sql) {
        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql);
             ResultSet resultSet = statement.executeQuery()) {
            resultSet.next();
            return resultSet.getInt(1);
        } catch (Exception exception) {
            throw new RuntimeException("Unable to count properties.", exception);
        }
    }

    private List<Property> queryList(String sql, StatementConfigurer configurer) {
        try (Connection connection = DatabaseUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            configurer.configure(statement);
            try (ResultSet resultSet = statement.executeQuery()) {
                List<Property> properties = new ArrayList<>();
                while (resultSet.next()) {
                    properties.add(map(resultSet));
                }
                return properties;
            }
        } catch (Exception exception) {
            throw new RuntimeException("Unable to query properties.", exception);
        }
    }

    private void bind(PreparedStatement statement, List<Object> params) throws Exception {
        for (int index = 0; index < params.size(); index++) {
            Object value = params.get(index);
            int position = index + 1;
            if (value instanceof Integer) {
                statement.setInt(position, (Integer) value);
            } else if (value instanceof Double) {
                statement.setDouble(position, (Double) value);
            } else {
                statement.setString(position, String.valueOf(value));
            }
        }
    }

    private void setNullableInteger(PreparedStatement statement, int index, Integer value) throws Exception {
        if (value == null) {
            statement.setNull(index, java.sql.Types.INTEGER);
        } else {
            statement.setInt(index, value);
        }
    }

    private boolean hasValue(String value) {
        return value != null && !value.trim().isEmpty();
    }

    private Property map(ResultSet resultSet) throws Exception {
        Property property = new Property();
        property.setId(resultSet.getInt("id"));
        property.setUserId(resultSet.getInt("user_id"));
        property.setTitle(resultSet.getString("title"));
        property.setCity(resultSet.getString("city"));
        property.setLocation(resultSet.getString("location"));
        property.setPrice(resultSet.getDouble("price"));
        property.setPropertyType(resultSet.getString("property_type"));
        property.setPurpose(resultSet.getString("purpose"));
        property.setBedrooms((Integer) resultSet.getObject("bedrooms"));
        property.setBathrooms((Integer) resultSet.getObject("bathrooms"));
        property.setAreaSqft((Integer) resultSet.getObject("area_sqft"));
        property.setDescription(resultSet.getString("description"));
        property.setImage(resultSet.getString("image"));
        property.setStatus(resultSet.getString("status"));
        property.setOwnerName(resultSet.getString("owner_name"));
        property.setOwnerEmail(resultSet.getString("owner_email"));
        Timestamp created = resultSet.getTimestamp("created_at");
        Timestamp updated = resultSet.getTimestamp("updated_at");
        if (created != null) {
            property.setCreatedAt(created.toLocalDateTime());
        }
        if (updated != null) {
            property.setUpdatedAt(updated.toLocalDateTime());
        }
        return property;
    }

    @FunctionalInterface
    private interface StatementConfigurer {
        void configure(PreparedStatement statement) throws Exception;
    }
}
