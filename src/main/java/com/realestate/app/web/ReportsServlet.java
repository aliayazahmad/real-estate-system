package com.realestate.app.web;

import com.realestate.app.dao.BookingDao;
import com.realestate.app.dao.PaymentDao;
import com.realestate.app.dao.PropertyDao;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import java.io.IOException;

@WebServlet(name = "ReportsServlet", urlPatterns = {"/admin/reports"})
public class ReportsServlet extends BaseServlet {
    private final PropertyDao propertyDao = new PropertyDao();
    private final BookingDao bookingDao = new BookingDao();
    private final PaymentDao paymentDao = new PaymentDao();

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        if (!requireRole(request, response, "admin")) {
            return;
        }
        request.setAttribute("pageTitle", "Reports & Analytics");
        request.setAttribute("propertyStatusCounts", propertyDao.statusCounts());
        request.setAttribute("bookingStatusCounts", bookingDao.statusCounts());
        request.setAttribute("topCities", propertyDao.topCities(10));
        request.setAttribute("recentPayments", paymentDao.recent(10));
        request.setAttribute("totalRevenue", paymentDao.totalRevenue());
        render(request, response, "reports.jsp");
    }
}
