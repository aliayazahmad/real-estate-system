package com.realestate.app.web;

import com.realestate.app.dao.BookingDao;
import com.realestate.app.dao.PaymentDao;
import com.realestate.app.dao.PropertyDao;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import java.io.IOException;
import java.util.LinkedHashMap;
import java.util.Map;

@WebServlet(name = "HomeServlet", urlPatterns = {"/"})
public class HomeServlet extends BaseServlet {
    private final PropertyDao propertyDao = new PropertyDao();
    private final BookingDao bookingDao = new BookingDao();
    private final PaymentDao paymentDao = new PaymentDao();

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        Map<String, Integer> stats = new LinkedHashMap<>();
        stats.put("Approved Properties", propertyDao.countApproved());
        stats.put("Bookings", bookingDao.countAll());
        stats.put("Paid Payments", paymentDao.countPaid());
        request.setAttribute("stats", stats);
        request.setAttribute("featuredProperties", propertyDao.latestApproved(3));
        render(request, response, "home.jsp");
    }
}
