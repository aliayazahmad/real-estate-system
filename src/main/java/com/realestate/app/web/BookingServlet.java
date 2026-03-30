package com.realestate.app.web;

import com.realestate.app.dao.BookingDao;
import com.realestate.app.dao.PropertyDao;
import com.realestate.app.model.Booking;
import com.realestate.app.model.Property;
import com.realestate.app.model.SessionUser;
import com.realestate.app.util.FlashUtil;
import com.realestate.app.util.ValidationUtil;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import java.io.IOException;
import java.time.LocalDate;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.List;
import java.util.Optional;

@WebServlet(name = "BookingServlet", urlPatterns = {"/bookings", "/bookings/new", "/bookings/cancel", "/bookings/status"})
public class BookingServlet extends BaseServlet {
    private final BookingDao bookingDao = new BookingDao();
    private final PropertyDao propertyDao = new PropertyDao();

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        String path = request.getServletPath();
        if ("/bookings/new".equals(path)) {
            if (!requireRole(request, response, "customer")) {
                return;
            }
            renderCreateForm(request, response);
            return;
        }
        if (!requireLogin(request, response)) {
            return;
        }
        renderBookings(request, response);
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        String path = request.getServletPath();
        if (!validateCsrf(request, response)) {
            return;
        }
        if ("/bookings/new".equals(path)) {
            if (!requireRole(request, response, "customer")) {
                return;
            }
            handleCreate(request, response);
            return;
        }
        if ("/bookings/cancel".equals(path)) {
            if (!requireRole(request, response, "customer")) {
                return;
            }
            handleCancel(request, response);
            return;
        }
        if ("/bookings/status".equals(path)) {
            if (!requireRole(request, response, "agent", "admin")) {
                return;
            }
            handleStatusUpdate(request, response);
        }
    }

    private void renderBookings(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        SessionUser user = currentUser(request);
        request.setAttribute("pageTitle", "Bookings");
        if ("admin".equalsIgnoreCase(user.getRole())) {
            request.setAttribute("adminMode", Boolean.TRUE);
            request.setAttribute("agentMode", Boolean.FALSE);
            request.setAttribute("bookings", bookingDao.pendingForAdmin(50));
        } else if ("agent".equalsIgnoreCase(user.getRole())) {
            request.setAttribute("adminMode", Boolean.FALSE);
            request.setAttribute("agentMode", Boolean.TRUE);
            request.setAttribute("bookings", bookingDao.recentForAgent(user.getId(), 50));
        } else {
            request.setAttribute("adminMode", Boolean.FALSE);
            request.setAttribute("agentMode", Boolean.FALSE);
            request.setAttribute("bookings", bookingDao.recentForUser(user.getId(), 50));
        }
        request.setAttribute("statusChoices", statusChoices());
        render(request, response, "bookings.jsp");
    }

    private void renderCreateForm(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        Optional<Property> propertyOptional = propertyDao.findById(parseId(request.getParameter("propertyId")));
        if (!propertyOptional.isPresent() || !"approved".equalsIgnoreCase(propertyOptional.get().getStatus())) {
            FlashUtil.error(request, "This property is not available for booking.");
            redirect(request, response, "/properties");
            return;
        }
        request.setAttribute("pageTitle", "Book Property Visit");
        request.setAttribute("bookingProperty", propertyOptional.get());
        render(request, response, "booking-form.jsp");
    }

    private void handleCreate(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        SessionUser user = currentUser(request);
        Optional<Property> propertyOptional = propertyDao.findById(parseId(request.getParameter("propertyId")));
        if (!propertyOptional.isPresent() || !"approved".equalsIgnoreCase(propertyOptional.get().getStatus())) {
            FlashUtil.error(request, "This property is not available for booking.");
            redirect(request, response, "/properties");
            return;
        }

        Property property = propertyOptional.get();
        Booking booking = new Booking();
        booking.setPropertyId(property.getId());
        booking.setUserId(user.getId());
        booking.setBookingDate(LocalDate.now());
        booking.setVisitDate(parseDate(request.getParameter("visitDate")));
        booking.setMessage(safe(request.getParameter("message")));
        booking.setStatus("pending");

        List<String> errors = new ArrayList<>();
        if (booking.getVisitDate() == null) {
            errors.add("Visit date is required.");
        } else if (booking.getVisitDate().isBefore(LocalDate.now())) {
            errors.add("Visit date cannot be in the past.");
        }
        if (bookingDao.hasActiveRequest(user.getId(), property.getId())) {
            errors.add("You already have an active booking request for this property.");
        }

        if (!errors.isEmpty()) {
            request.setAttribute("errors", errors);
            request.setAttribute("bookingProperty", property);
            request.setAttribute("pageTitle", "Book Property Visit");
            render(request, response, "booking-form.jsp");
            return;
        }

        bookingDao.create(booking);
        FlashUtil.success(request, "Booking request submitted successfully.");
        redirect(request, response, "/bookings");
    }

    private void handleCancel(HttpServletRequest request, HttpServletResponse response) throws IOException {
        SessionUser user = currentUser(request);
        Optional<Booking> bookingOptional = bookingDao.findByIdForUser(parseId(request.getParameter("id")), user.getId());
        if (!bookingOptional.isPresent()) {
            FlashUtil.error(request, "Booking not found.");
            redirect(request, response, "/bookings");
            return;
        }

        Booking booking = bookingOptional.get();
        if (!Arrays.asList("pending", "confirmed").contains(booking.getStatus())) {
            FlashUtil.error(request, "Only pending or confirmed bookings can be cancelled.");
            redirect(request, response, "/bookings");
            return;
        }

        bookingDao.updateStatus(booking.getId(), "cancelled");
        Optional<Property> propertyOptional = propertyDao.findById(booking.getPropertyId());
        if (propertyOptional.isPresent() && "booked".equalsIgnoreCase(propertyOptional.get().getStatus())) {
            propertyDao.updateStatus(propertyOptional.get().getId(), "approved");
        }

        FlashUtil.success(request, "Booking cancelled successfully.");
        redirect(request, response, "/bookings");
    }

    private void handleStatusUpdate(HttpServletRequest request, HttpServletResponse response) throws IOException {
        SessionUser user = currentUser(request);
        Optional<Booking> bookingOptional = bookingDao.findById(parseId(request.getParameter("id")));
        if (!bookingOptional.isPresent()) {
            FlashUtil.error(request, "Booking not found.");
            redirect(request, response, "/bookings");
            return;
        }

        Booking booking = bookingOptional.get();
        Optional<Property> propertyOptional = propertyDao.findById(booking.getPropertyId());
        if (!propertyOptional.isPresent()) {
            FlashUtil.error(request, "Related property not found.");
            redirect(request, response, "/bookings");
            return;
        }

        Property property = propertyOptional.get();
        if ("agent".equalsIgnoreCase(user.getRole()) && property.getUserId() != user.getId()) {
            FlashUtil.error(request, "You cannot manage this booking.");
            redirect(request, response, "/bookings");
            return;
        }

        String status = safe(request.getParameter("status")).toLowerCase();
        if (!statusChoices().contains(status)) {
            FlashUtil.error(request, "Invalid booking status.");
            redirect(request, response, "/bookings");
            return;
        }

        bookingDao.updateStatus(booking.getId(), status);
        if ("confirmed".equals(status)) {
            propertyDao.updateStatus(property.getId(), "booked");
            bookingDao.cancelPendingForProperty(property.getId(), booking.getId());
        } else if ("cancelled".equals(status) || "rejected".equals(status)) {
            if ("booked".equalsIgnoreCase(property.getStatus())) {
                propertyDao.updateStatus(property.getId(), "approved");
            }
        } else if ("pending".equals(status) && !"rejected".equalsIgnoreCase(property.getStatus())) {
            propertyDao.updateStatus(property.getId(), "approved");
        }

        FlashUtil.success(request, "Booking status updated.");
        redirect(request, response, "/bookings");
    }

    private List<String> statusChoices() {
        return Arrays.asList("pending", "confirmed", "completed", "cancelled", "rejected");
    }

    private int parseId(String value) {
        try {
            return Integer.parseInt(value == null ? "0" : value.trim());
        } catch (Exception exception) {
            return 0;
        }
    }

    private LocalDate parseDate(String value) {
        try {
            return ValidationUtil.parseDate(value);
        } catch (Exception exception) {
            return null;
        }
    }

    private String safe(String value) {
        return value == null ? "" : value.trim();
    }
}
