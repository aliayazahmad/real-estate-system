package com.realestate.app.web;

import com.realestate.app.dao.PropertyDao;
import com.realestate.app.model.Property;
import com.realestate.app.model.SessionUser;
import com.realestate.app.util.FlashUtil;
import com.realestate.app.util.UploadUtil;
import com.realestate.app.util.ValidationUtil;

import javax.servlet.ServletException;
import javax.servlet.annotation.MultipartConfig;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.http.Part;
import java.io.IOException;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import java.util.Optional;

@WebServlet(name = "PropertyServlet", urlPatterns = {"/properties", "/properties/new", "/properties/edit", "/properties/delete"})
@MultipartConfig(maxFileSize = 5 * 1024 * 1024, maxRequestSize = 6 * 1024 * 1024)
public class PropertyServlet extends BaseServlet {
    private final PropertyDao propertyDao = new PropertyDao();

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        String path = request.getServletPath();
        if ("/properties/new".equals(path)) {
            if (!requireRole(request, response, "agent", "admin")) {
                return;
            }
            renderForm(request, response, new Property(), Boolean.FALSE);
            return;
        }
        if ("/properties/edit".equals(path)) {
            if (!requireRole(request, response, "agent", "admin")) {
                return;
            }
            renderEdit(request, response);
            return;
        }
        renderList(request, response);
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        String path = request.getServletPath();
        if (!validateCsrf(request, response)) {
            return;
        }

        if ("/properties/new".equals(path)) {
            if (!requireRole(request, response, "agent", "admin")) {
                return;
            }
            handleCreate(request, response);
            return;
        }
        if ("/properties/edit".equals(path)) {
            if (!requireRole(request, response, "agent", "admin")) {
                return;
            }
            handleUpdate(request, response);
            return;
        }
        if ("/properties/delete".equals(path)) {
            if (!requireRole(request, response, "agent", "admin")) {
                return;
            }
            handleDelete(request, response);
        }
    }

    private void renderList(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        Map<String, String> filters = new LinkedHashMap<>();
        filters.put("q", safe(request.getParameter("q")));
        filters.put("city", safe(request.getParameter("city")));
        filters.put("property_type", safe(request.getParameter("property_type")));
        filters.put("purpose", safe(request.getParameter("purpose")));
        filters.put("min_price", safe(request.getParameter("min_price")));
        filters.put("max_price", safe(request.getParameter("max_price")));
        filters.put("status", safe(request.getParameter("status")));

        request.setAttribute("pageTitle", "Property Catalogue");
        request.setAttribute("filters", filters);
        request.setAttribute("properties", propertyDao.search(filters, currentUser(request)));
        populateFormMeta(request);
        render(request, response, "properties.jsp");
    }

    private void renderEdit(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        Optional<Property> propertyOptional = propertyDao.findById(parseId(request.getParameter("id")));
        if (!propertyOptional.isPresent()) {
            FlashUtil.error(request, "Property not found.");
            redirect(request, response, "/properties");
            return;
        }
        Property property = propertyOptional.get();
        if (!canManage(currentUser(request), property)) {
            FlashUtil.error(request, "You cannot edit this property.");
            redirect(request, response, "/properties");
            return;
        }
        renderForm(request, response, property, Boolean.TRUE);
    }

    private void renderForm(HttpServletRequest request, HttpServletResponse response, Property property, Boolean editing) throws ServletException, IOException {
        request.setAttribute("pageTitle", editing ? "Edit Property" : "Add Property");
        request.setAttribute("editing", editing);
        request.setAttribute("propertyForm", property);
        populateFormMeta(request);
        render(request, response, "property-form.jsp");
    }

    private void handleCreate(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        Property property = buildProperty(request, new Property());
        property.setUserId(currentUser(request).getId());
        property.setStatus(resolveSubmittedStatus(request, null));

        List<String> errors = validateProperty(request, property, null);
        if (!errors.isEmpty()) {
            request.setAttribute("errors", errors);
            renderForm(request, response, property, Boolean.FALSE);
            return;
        }

        String imageName = saveImageIfPresent(request, errors);
        if (!errors.isEmpty()) {
            request.setAttribute("errors", errors);
            renderForm(request, response, property, Boolean.FALSE);
            return;
        }

        property.setImage(imageName);
        propertyDao.create(property);
        FlashUtil.success(request, "Property submitted successfully.");
        redirect(request, response, "/properties");
    }

    private void handleUpdate(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        Optional<Property> propertyOptional = propertyDao.findById(parseId(request.getParameter("id")));
        if (!propertyOptional.isPresent()) {
            FlashUtil.error(request, "Property not found.");
            redirect(request, response, "/properties");
            return;
        }

        Property existing = propertyOptional.get();
        if (!canManage(currentUser(request), existing)) {
            FlashUtil.error(request, "You cannot update this property.");
            redirect(request, response, "/properties");
            return;
        }

        Property property = buildProperty(request, existing);
        property.setId(existing.getId());
        property.setUserId(existing.getUserId());
        property.setStatus(resolveSubmittedStatus(request, existing.getStatus()));

        List<String> errors = validateProperty(request, property, existing.getId());
        if (!errors.isEmpty()) {
            request.setAttribute("errors", errors);
            renderForm(request, response, property, Boolean.TRUE);
            return;
        }

        String newImage = saveImageIfPresent(request, errors);
        if (!errors.isEmpty()) {
            request.setAttribute("errors", errors);
            renderForm(request, response, property, Boolean.TRUE);
            return;
        }
        if (newImage != null) {
            property.setImage(newImage);
        }

        propertyDao.update(property);
        if (newImage != null) {
            UploadUtil.deleteImage(getServletContext(), existing.getImage());
        }

        FlashUtil.success(request, "Property updated successfully.");
        redirect(request, response, "/properties");
    }

    private void handleDelete(HttpServletRequest request, HttpServletResponse response) throws IOException {
        Optional<Property> propertyOptional = propertyDao.findById(parseId(request.getParameter("id")));
        if (!propertyOptional.isPresent()) {
            FlashUtil.error(request, "Property not found.");
            redirect(request, response, "/properties");
            return;
        }

        Property property = propertyOptional.get();
        if (!canManage(currentUser(request), property)) {
            FlashUtil.error(request, "You cannot delete this property.");
            redirect(request, response, "/properties");
            return;
        }
        if (propertyDao.hasBookings(property.getId())) {
            FlashUtil.error(request, "This property already has bookings and cannot be deleted.");
            redirect(request, response, "/properties");
            return;
        }

        propertyDao.delete(property.getId());
        UploadUtil.deleteImage(getServletContext(), property.getImage());
        FlashUtil.success(request, "Property deleted successfully.");
        redirect(request, response, "/properties");
    }

    private Property buildProperty(HttpServletRequest request, Property property) {
        property.setTitle(safe(request.getParameter("title")));
        property.setCity(safe(request.getParameter("city")));
        property.setLocation(safe(request.getParameter("location")));
        property.setPrice(parseDouble(request.getParameter("price")));
        property.setPropertyType(safe(request.getParameter("propertyType")));
        property.setPurpose(safe(request.getParameter("purpose")));
        property.setBedrooms(parseInteger(request.getParameter("bedrooms")));
        property.setBathrooms(parseInteger(request.getParameter("bathrooms")));
        property.setAreaSqft(parseInteger(request.getParameter("areaSqft")));
        property.setDescription(safe(request.getParameter("description")));
        return property;
    }

    private List<String> validateProperty(HttpServletRequest request, Property property, Integer excludeId) throws IOException, ServletException {
        List<String> errors = new ArrayList<>();
        if (property.getTitle().isEmpty()) {
            errors.add("Property title is required.");
        }
        if (property.getCity().isEmpty()) {
            errors.add("City is required.");
        }
        if (property.getLocation().isEmpty()) {
            errors.add("Location is required.");
        }
        if (property.getPrice() <= 0) {
            errors.add("Price must be greater than zero.");
        }
        if (!propertyTypes().contains(property.getPropertyType())) {
            errors.add("Choose a valid property type.");
        }
        if (!purposeOptions().contains(property.getPurpose())) {
            errors.add("Choose a valid purpose.");
        }
        if (propertyDao.existsByTitleAndLocation(property.getTitle(), property.getLocation(), excludeId)) {
            errors.add("A similar property already exists for this location.");
        }

        Part imagePart = request.getPart("image");
        if (imagePart != null && imagePart.getSize() > 0) {
            String contentType = imagePart.getContentType();
            if (contentType == null || (!"image/jpeg".equals(contentType) && !"image/png".equals(contentType) && !"image/webp".equals(contentType))) {
                errors.add("Only JPG, PNG, and WEBP images are allowed.");
            }
        }
        return errors;
    }

    private String saveImageIfPresent(HttpServletRequest request, List<String> errors) throws IOException, ServletException {
        Part imagePart = request.getPart("image");
        if (imagePart == null || imagePart.getSize() == 0) {
            return null;
        }
        try {
            return UploadUtil.saveImage(imagePart, getServletContext());
        } catch (IOException exception) {
            errors.add(exception.getMessage());
            return null;
        }
    }

    private String resolveSubmittedStatus(HttpServletRequest request, String currentStatus) {
        SessionUser user = currentUser(request);
        if (user != null && "admin".equalsIgnoreCase(user.getRole())) {
            String submitted = safe(request.getParameter("status"));
            if (statusOptions().contains(submitted)) {
                return submitted;
            }
            return currentStatus == null || currentStatus.trim().isEmpty() ? "approved" : currentStatus;
        }
        return "pending";
    }

    private boolean canManage(SessionUser user, Property property) {
        return user != null && ("admin".equalsIgnoreCase(user.getRole()) || property.getUserId() == user.getId());
    }

    private void populateFormMeta(HttpServletRequest request) {
        request.setAttribute("cityOptions", propertyDao.topCities(20));
        request.setAttribute("propertyTypes", propertyTypes());
        request.setAttribute("purposeOptions", purposeOptions());
        request.setAttribute("statusOptions", statusOptions());
    }

    private List<String> propertyTypes() {
        return Arrays.asList("House", "Apartment", "Plot", "Commercial", "Villa");
    }

    private List<String> purposeOptions() {
        return Arrays.asList("Sale", "Rent");
    }

    private List<String> statusOptions() {
        return Arrays.asList("pending", "approved", "booked", "rejected");
    }

    private int parseId(String value) {
        try {
            return Integer.parseInt(value == null ? "0" : value.trim());
        } catch (Exception exception) {
            return 0;
        }
    }

    private Integer parseInteger(String value) {
        try {
            return ValidationUtil.parseInteger(value);
        } catch (Exception exception) {
            return null;
        }
    }

    private double parseDouble(String value) {
        try {
            return Double.parseDouble(value == null ? "0" : value.trim());
        } catch (Exception exception) {
            return 0;
        }
    }

    private String safe(String value) {
        return value == null ? "" : value.trim();
    }
}
