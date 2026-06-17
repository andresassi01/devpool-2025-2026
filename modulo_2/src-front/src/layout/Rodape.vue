<template>
  <footer class="footer site-footer">
    <div class="container has-text-centered">
      <div class="content">
        <p>
          <strong>NOME</strong> - Links e Copyright © 2026
        </p>
        <div class="buttons is-centered mt-4">
          <a class="button is-light is-rounded" aria-label="Facebook">
            <span class="icon"><i class="fab fa-facebook"></i></span>
          </a>
          <a class="button is-light is-rounded" aria-label="Instagram">
            <span class="icon"><i class="fab fa-instagram"></i></span>
          </a>
          <a class="button is-light is-rounded" aria-label="Linkedin">
            <span class="icon"><i class="fab fa-linkedin"></i></span>
          </a>
        </div>
      </div>
    </div>
  </footer>
</template>

<style scoped>
.site-footer {
  background-color: #fafafa;
  border-top: 1px solid #eeeeee;
  padding: 4rem 1.5rem;
}
.button.is-light:hover {
  background-color: #f0f0f0;
  color: #27ae60;
  transform: translateY(-2px);
  transition: all 0.3s ease;
}
</style>