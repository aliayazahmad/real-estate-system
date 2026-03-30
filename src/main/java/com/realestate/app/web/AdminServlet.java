package com.realestate.app.web;

import com.realestate.app.dao.BookingDao;
import com.realestate.app.dao.PaymentDao;
import com.realestate.app.dao.PropertyDao;
import com.realestate.app.dao.UserDao;
import com.realestate.app.util.FlashUtil;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import java.io.IOException;
import java.util.Arrays;
import java.util.LinkedHashMap;
import java.util.Map;

@WebServlet(name = "AdminServlet", urlPatterns = {"/admin", "/admin/properties/status"})
public class AdminServlet extends BaseServlet {
    private final UserDao userDao = new UserDao();
    private final PropertyDao propertyDao = new PropertyDao();
    private final BookingDao bookingDao = new BookingDao();
    private final PaymentDao paymentDao = new PaymentDao();

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        if (!requireRole(request, response, "admin")) {
            return;
        }
        request.setAttribute("pageTitle", "Admin Control Center");
        request.setAttribute("dashboardStats", buildStats());
        request.setAttribute("pendingProperties", propertyDao.pendingApproval(8));
        request.setAttribute("pendingBookings", bookingDao.pendingForAdmin(8));
        request.setAttribute("recentPayments", paymentDao.recent(8));
        request.setAttribute("propertyStatusCounts", propertyDao.statusCounts());
        request.setAttribute("bookingStatusCounts", bookingDao.statusCounts());
        render(request, response, "admin.jsp");
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        if (!requireRole(request, response, "admin") || !validateCsrf(request, response)) {
            return;
        }
        if ("/admin/properties/status".equals(request.getServletPath())) {
            handlePropertyStatus(request, response);
        }
    }

    private Map<String, String> buildStats() {
        Map<String, String> stats = new LinkedHashMap<>();
        stats.put("Users", String.valueOf(userDao.countAll()));
        stats.put("Properties", String.valueOf(propertyDao.countAll()));
        stats.put("Bookings", String.valueOf(bookingDao.countAll()));
        stats.put("Paid Payments", String.valueOf(paymentDao.countPaid()));
        stats.put("Revenue", String.format("Rs. %.2f", paymentDao.totalRevenue()));
        return stats;
    }

    private void handlePropertyStatus(HttpServletRequest request, HttpServletResponse response) throws IOException {
        int propertyId = parseId(request.getParameter("id"));
        String status = safe(request.getParameter("status")).toLowerCase();
        if (propertyId <= 0 || !Arrays.asList("pending", "approved", "booked", "rejected").contains(status)) {
            FlashUtil.error(request, "Invalid property update request.");
            redirect(request, response, "/admin");
            return;
        }
        propertyDao.updateStatus(propertyId, status);
        FlashUtil.success(request, "Property status updated.");
        redirect(request, response, "/admin");
    }

    private int parseId(String value) {
        try {
            return Integer.parseInt(value == null ? "0" : value.trim());
        } catch (Exception exception) {
            return 0;
        }
    }

    private String safe(String value) {
        return value == null ? "" : value.trim();
    }
}
