package com.realestate.app.web;

import com.realestate.app.dao.BookingDao;
import com.realestate.app.dao.PaymentDao;
import com.realestate.app.model.Booking;
import com.realestate.app.model.Payment;
import com.realestate.app.model.SessionUser;
import com.realestate.app.util.FlashUtil;
import com.realestate.app.util.ValidationUtil;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import java.io.IOException;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import java.util.ArrayList;
import java.util.List;
import java.util.Optional;

@WebServlet(name = "PaymentServlet", urlPatterns = {"/payments", "/payments/new", "/payments/receipt"})
public class PaymentServlet extends BaseServlet {
    private final PaymentDao paymentDao = new PaymentDao();
    private final BookingDao bookingDao = new BookingDao();

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        String path = request.getServletPath();
        if ("/payments/new".equals(path)) {
            if (!requireRole(request, response, "customer")) {
                return;
            }
            renderPaymentForm(request, response);
            return;
        }
        if ("/payments/receipt".equals(path)) {
            if (!requireLogin(request, response)) {
                return;
            }
            renderReceipt(request, response);
            return;
        }
        if (!requireLogin(request, response)) {
            return;
        }
        renderPayments(request, response);
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        if (!validateCsrf(request, response)) {
            return;
        }
        if (!"/payments/new".equals(request.getServletPath())) {
            return;
        }
        if (!requireRole(request, response, "customer")) {
            return;
        }
        handleCreate(request, response);
    }

    private void renderPayments(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        SessionUser user = currentUser(request);
        request.setAttribute("pageTitle", "Payments");
        request.setAttribute("adminMode", "admin".equalsIgnoreCase(user.getRole()));
        request.setAttribute("payments", "admin".equalsIgnoreCase(user.getRole()) ? paymentDao.recent(50) : paymentDao.listByUser(user.getId(), 50));
        render(request, response, "payments.jsp");
    }

    private void renderPaymentForm(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        Optional<Booking> bookingOptional = bookingDao.findByIdForUser(parseId(request.getParameter("bookingId")), currentUser(request).getId());
        if (!bookingOptional.isPresent()) {
            FlashUtil.error(request, "Booking not found.");
            redirect(request, response, "/bookings");
            return;
        }
        Booking booking = bookingOptional.get();
        if (!"confirmed".equalsIgnoreCase(booking.getStatus())) {
            FlashUtil.error(request, "Only confirmed bookings can be paid.");
            redirect(request, response, "/bookings");
            return;
        }
        if (paymentDao.findByBookingId(booking.getId()).isPresent()) {
            FlashUtil.error(request, "This booking already has a payment receipt.");
            redirect(request, response, "/payments");
            return;
        }
        request.setAttribute("pageTitle", "Record Payment");
        request.setAttribute("paymentBooking", booking);
        render(request, response, "payment-form.jsp");
    }

    private void handleCreate(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        SessionUser user = currentUser(request);
        Optional<Booking> bookingOptional = bookingDao.findByIdForUser(parseId(request.getParameter("bookingId")), user.getId());
        if (!bookingOptional.isPresent()) {
            FlashUtil.error(request, "Booking not found.");
            redirect(request, response, "/bookings");
            return;
        }

        Booking booking = bookingOptional.get();
        if (!"confirmed".equalsIgnoreCase(booking.getStatus())) {
            FlashUtil.error(request, "Only confirmed bookings can be paid.");
            redirect(request, response, "/bookings");
            return;
        }
        if (paymentDao.findByBookingId(booking.getId()).isPresent()) {
            FlashUtil.error(request, "This booking already has a payment receipt.");
            redirect(request, response, "/payments");
            return;
        }

        String amountRaw = safe(request.getParameter("amount"));
        String method = safe(request.getParameter("paymentMethod"));
        String transactionRef = safe(request.getParameter("transactionRef"));
        String notes = safe(request.getParameter("notes"));

        List<String> errors = new ArrayList<>();
        if (!ValidationUtil.isPositiveAmount(amountRaw)) {
            errors.add("Enter a valid payment amount.");
        }
        if (method.isEmpty()) {
            errors.add("Payment method is required.");
        }
        if (transactionRef.isEmpty()) {
            errors.add("Transaction reference is required.");
        }

        if (!errors.isEmpty()) {
            request.setAttribute("errors", errors);
            request.setAttribute("paymentBooking", booking);
            request.setAttribute("pageTitle", "Record Payment");
            render(request, response, "payment-form.jsp");
            return;
        }

        Payment payment = new Payment();
        payment.setBookingId(booking.getId());
        payment.setAmount(Double.parseDouble(amountRaw));
        payment.setPaymentMethod(method);
        payment.setTransactionRef(transactionRef);
        payment.setPaymentDate(LocalDateTime.now());
        payment.setStatus("paid");
        payment.setInvoiceNumber(generateInvoiceNumber());
        payment.setNotes(notes);

        int paymentId = paymentDao.create(payment);
        bookingDao.updateStatus(booking.getId(), "completed");
        FlashUtil.success(request, "Payment recorded successfully.");
        redirect(request, response, "/payments/receipt?id=" + paymentId);
    }

    private void renderReceipt(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        SessionUser user = currentUser(request);
        Optional<Payment> paymentOptional = paymentDao.findReceipt(parseId(request.getParameter("id")));
        if (!paymentOptional.isPresent()) {
            FlashUtil.error(request, "Receipt not found.");
            redirect(request, response, "/payments");
            return;
        }

        Payment payment = paymentOptional.get();
        if (!"admin".equalsIgnoreCase(user.getRole()) && payment.getUserId() != user.getId()) {
            FlashUtil.error(request, "You cannot view this receipt.");
            redirect(request, response, "/payments");
            return;
        }

        request.setAttribute("pageTitle", "Payment Receipt");
        request.setAttribute("paymentReceipt", payment);
        render(request, response, "payment-receipt.jsp");
    }

    private String generateInvoiceNumber() {
        String timestamp = LocalDateTime.now().format(DateTimeFormatter.ofPattern("yyyyMMddHHmmss"));
        return "INV-" + timestamp;
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
