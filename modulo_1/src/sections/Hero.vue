<template>
  <section class="hero is-fullheight-with-navbar is-white" id="inicio">
    <div class="hero-body">
      <div class="container">
        <div class="columns is-vcentered is-variable is-8">
          <div class="column is-6 has-text-centered">
            <h1 class="title is-1 has-text-weight-bold main-title">
              A melhor gestão para
              <span class="text-gradient">sua empresa</span>
            </h1>
            <div class="buttons is-centered mt-6">
              <a class="button is-success is-large btn-principal" href="#contato">
                <strong>EXPERIMENTE GRÁTIS</strong>
                <span class="icon"><i class="fas fa-rocket"></i></span>
              </a>
            </div>
          </div>

          <div class="column is-6">
            <div class="image-container">
              <img src="../assets/images/imgprincipal.png" alt="Dashboard do Sistema" class="main-image" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.main-title {
  font-size: clamp(2.5rem, 8vw, 4rem) !important; 
  line-height: 1.1;
}

.text-gradient {
  background: linear-gradient(90deg, #2ecc71, #27ae60);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
}

.image-container {
  position: relative;
  padding: 10px;
  transition: transform 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.image-container:hover {
  transform: perspective(1000px) rotateY(-5deg) rotateX(2deg);
}

.main-image {
  border-radius: 16px;
  box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

@media screen and (max-width: 768px) {
  .image-container { margin-top: 2rem; }
}
</style>