package com.realestate.app.web;

import com.realestate.app.dao.BookingDao;
import com.realestate.app.dao.PaymentDao;
import com.realestate.app.dao.PropertyDao;
import com.realestate.app.dao.UserDao;
import com.realestate.app.model.SessionUser;
import com.realestate.app.model.User;
import com.realestate.app.util.FlashUtil;
import com.realestate.app.util.PasswordUtil;
import com.realestate.app.util.ValidationUtil;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import java.io.IOException;
import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import java.util.Optional;

@WebServlet(name = "DashboardServlet", urlPatterns = {"/dashboard", "/profile"})
public class DashboardServlet extends BaseServlet {
    private final UserDao userDao = new UserDao();
    private final PropertyDao propertyDao = new PropertyDao();
    private final BookingDao bookingDao = new BookingDao();
    private final PaymentDao paymentDao = new PaymentDao();

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        SessionUser currentUser = currentUser(request);
        if (!requireLogin(request, response)) {
            return;
        }
        if ("admin".equalsIgnoreCase(currentUser.getRole()) && "/dashboard".equals(request.getServletPath())) {
            redirect(request, response, "/admin");
            return;
        }
        if ("/profile".equals(request.getServletPath())) {
            renderProfile(request, response);
            return;
        }
        renderDashboard(request, response, currentUser);
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        if (!requireLogin(request, response) || !validateCsrf(request, response)) {
            return;
        }
        if ("/profile".equals(request.getServletPath())) {
            handleProfileUpdate(request, response);
        }
    }

    private void renderDashboard(HttpServletRequest request, HttpServletResponse response, SessionUser currentUser) throws ServletException, IOException {
        Map<String, Integer> stats = new LinkedHashMap<>();
        if ("agent".equalsIgnoreCase(currentUser.getRole())) {
            stats.put("Properties", propertyDao.countByOwner(currentUser.getId(), null));
            stats.put("Pending Properties", propertyDao.countByOwner(currentUser.getId(), "pending"));
            stats.put("Booking Requests", bookingDao.countOpenForAgent(currentUser.getId()));
            request.setAttribute("agentMode", Boolean.TRUE);
            request.setAttribute("recentProperties", propertyDao.listByOwner(currentUser.getId(), 5));
            request.setAttribute("recentBookings", bookingDao.recentForAgent(currentUser.getId(), 5));
        } else {
            stats.put("Bookings", bookingDao.countByUser(currentUser.getId(), null));
            stats.put("Confirmed", bookingDao.countByUser(currentUser.getId(), "confirmed"));
            stats.put("Paid", paymentDao.countPaidByUser(currentUser.getId()));
            request.setAttribute("agentMode", Boolean.FALSE);
            request.setAttribute("recentBookings", bookingDao.recentForUser(currentUser.getId(), 5));
            request.setAttribute("recentProperties", propertyDao.latestApproved(4));
        }
        request.setAttribute("stats", stats);
        render(request, response, "dashboard.jsp");
    }

    private void renderProfile(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        Optional<User> userOptional = userDao.findById(currentUser(request).getId());
        if (!userOptional.isPresent()) {
            FlashUtil.error(request, "Profile not found.");
            redirect(request, response, "/dashboard");
            return;
        }
        request.setAttribute("profileUser", userOptional.get());
        render(request, response, "profile.jsp");
    }

    private void handleProfileUpdate(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        Optional<User> userOptional = userDao.findById(currentUser(request).getId());
        if (!userOptional.isPresent()) {
            FlashUtil.error(request, "Profile not found.");
            redirect(request, response, "/dashboard");
            return;
        }

        User user = userOptional.get();
        String name = safe(request.getParameter("name"));
        String phone = safe(request.getParameter("phone"));
        String password = safe(request.getParameter("password"));
        String confirmPassword = safe(request.getParameter("confirmPassword"));

        List<String> errors = new ArrayList<>();
        if (name.isEmpty()) {
            errors.add("Full name is required.");
        }
        if (!ValidationUtil.isPhone(phone)) {
            errors.add("Phone number must be a valid 10-digit Indian mobile number.");
        }
        if (!password.isEmpty() && !ValidationUtil.isStrongPassword(password)) {
            errors.add("New password must be at least 8 characters long.");
        }
        if (!password.equals(confirmPassword)) {
            errors.add("Password confirmation does not match.");
        }

        user.setName(name);
        user.setPhone(phone);

        if (!errors.isEmpty()) {
            request.setAttribute("errors", errors);
            request.setAttribute("profileUser", user);
            render(request, response, "profile.jsp");
            return;
        }

        boolean updatePassword = !password.isEmpty();
        if (updatePassword) {
            user.setPasswordHash(PasswordUtil.hashPassword(password));
        }
        userDao.updateProfile(user, updatePassword);

        SessionUser sessionUser = currentUser(request);
        sessionUser.setName(user.getName());
        sessionUser.setEmail(user.getEmail());
        FlashUtil.success(request, "Profile updated successfully.");
        redirect(request, response, "/profile");
    }

    private String safe(String value) {
        return value == null ? "" : value.trim();
    }
}
