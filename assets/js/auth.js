const sign_in_btn = document.querySelector("#sign-in-btn");
const sign_up_btn = document.querySelector("#sign-up-btn");
const container = document.querySelector(".container");

sign_up_btn.addEventListener("click", () => {
    container.classList.add("sign-up-mode");
    // Animation cho form đăng ký
    document.querySelector(".sign-up-form").style.animation = "fadeIn 0.5s forwards";
});

sign_in_btn.addEventListener("click", () => {
    container.classList.remove("sign-up-mode");
    // Animation cho form đăng nhập
    document.querySelector(".sign-in-form").style.animation = "fadeIn 0.5s forwards";
});

// Thêm hiệu ứng ripple cho nút
const buttons = document.querySelectorAll(".btn");
buttons.forEach(btn => {
    btn.addEventListener("click", function(e) {
        let x = e.clientX - e.target.offsetLeft;
        let y = e.clientY - e.target.offsetTop;
        
        let ripples = document.createElement("span");
        ripples.style.left = x + "px";
        ripples.style.top = y + "px";
        this.appendChild(ripples);
        
        setTimeout(() => {
            ripples.remove();
        }, 1000);
    });
});

// Animation cho input fields
const inputFields = document.querySelectorAll(".input-field");
inputFields.forEach(field => {
    field.addEventListener("focus", () => {
        field.style.transform = "scale(1.02)";
    });
    
    field.addEventListener("blur", () => {
        field.style.transform = "scale(1)";
    });
});

// Thêm hiệu ứng loading khi submit form
document.querySelectorAll("form").forEach(form => {
    form.addEventListener("submit", (e) => {
        e.preventDefault();
        const submitBtn = form.querySelector("input[type='submit']");
        submitBtn.value = "Đang xử lý...";
        submitBtn.style.opacity = "0.7";
        
        // Giả lập gửi form (thay thế bằng AJAX thực tế)
        setTimeout(() => {
            form.submit();
        }, 1000);
    });
}); 