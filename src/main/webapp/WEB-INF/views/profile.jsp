<%@ page import="java.util.List" %>
<%@ page import="com.realestate.app.model.User" %>
<%@ page import="com.realestate.app.util.ViewUtil" %>
<%
User profileUser = (User) request.getAttribute("profileUser");
List<String> errors = (List<String>) request.getAttribute("errors");
request.setAttribute("pageTitle", "Profile");
%>
<%@ include file="partials/header.jspf" %>

<section class="section-head">
    <div>
        <span class="eyebrow">Profile</span>
        <h1>Manage your account</h1>
        <p>Update your contact details and password without affecting your role access.</p>
    </div>
</section>

<section class="form-card wide-card">
    <h2>Profile details</h2>
    <% if (errors != null && !errors.isEmpty()) { %>
        <div class="inline-errors">
            <% for (String error : errors) { %>
                <p><%= ViewUtil.html(error) %></p>
            <% } %>
        </div>
    <% } %>
    <form method="post" action="<%= contextPath %>/profile">
        <input type="hidden" name="csrfToken" value="<%= csrfToken %>">
        <div class="form-grid">
            <div>
                <label>Full name</label>
                <input type="text" name="name" required value="<%= ViewUtil.html(profileUser.getName()) %>">
            </div>
            <div>
                <label>Email address</label>
                <input type="email" disabled value="<%= ViewUtil.html(profileUser.getEmail()) %>">
            </div>
            <div>
                <label>Phone number</label>
                <input type="text" name="phone" value="<%= ViewUtil.html(profileUser.getPhone()) %>">
            </div>
            <div>
                <label>Role</label>
                <input type="text" disabled value="<%= ViewUtil.html(profileUser.getRole()) %>">
            </div>
            <div>
                <label>New password</label>
                <input type="password" name="password" placeholder="Leave blank to keep current password">
            </div>
            <div>
                <label>Confirm new password</label>
                <input type="password" name="confirmPassword">
            </div>
        </div>
        <button class="btn btn-primary" type="submit">Save profile</button>
    </form>
</section>

<%@ include file="partials/footer.jspf" %>
