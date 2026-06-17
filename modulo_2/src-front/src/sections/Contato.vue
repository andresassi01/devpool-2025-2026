<template>
    <section class="section has-background-light" id="contato">
        <div class="container">
            <div class="box contact-box">
                <h2 class="title is-4 mb-5">Contato</h2>
                <form @submit.prevent="submeterFormulario">
                    <div class="columns is-multiline">
                        <div class="column is-4">
                            <CampoFormulario label="Nome*" icone="fas fa-user" :sucesso="form.nome.length >= 3">
                                <input v-model="form.nome" class="input" type="text" placeholder="Seu nome"
                                    :class="{ 'is-danger': foiTocado && !form.nome, 'is-success': form.nome.length >= 3 }">
                            </CampoFormulario>
                        </div>
                        <div class="column is-4">
                            <CampoFormulario label="E-mail*" icone="fas fa-envelope" :sucesso="emailValido">
                                <input v-model="form.email" class="input" type="email" placeholder="email@exemplo.com"
                                    :class="{ 'is-danger': (foiTocado && !form.email) || (form.email.length > 0 && !emailValido), 'is-success': emailValido }">
                                <template #ajuda>
                                    <p v-if="form.email.length > 0 && !emailValido" class="help is-danger">E-mail
                                        inválido.</p>
                                </template>
                            </CampoFormulario>
                        </div>
                        <div class="column is-4">
                            <CampoFormulario label="Telefone" icone="fas fa-phone">
                                <input :value="form.telefone" @input="aplicarMascaraTelefone" class="input" type="tel"
                                    placeholder="(00) 00000-0000">
                            </CampoFormulario>
                        </div>
                        <div class="column is-8">
                            <CampoFormulario label="Empresa*" icone="fas fa-briefcase"
                                :sucesso="form.empresa.length > 1">
                                <input v-model="form.empresa" class="input" type="text" placeholder="Nome da empresa"
                                    :class="{ 'is-danger': foiTocado && !form.empresa, 'is-success': form.empresa.length > 1 }">
                            </CampoFormulario>
                        </div>
                        <div class="column is-4">
                            <CampoFormulario label="Tamanho">
                                <div class="select is-fullwidth">
                                    <select v-model="form.tamanho">
                                        <option value="" disabled>Selecione...</option>
                                        <option value="1-10">1-10 funcionários</option>
                                        <option value="11-50">11-50 funcionários</option>
                                        <option value="50+">Mais de 50</option>
                                    </select>
                                </div>
                            </CampoFormulario>
                        </div>

                        <div class="column is-12">
                            <CampoFormulario label="Mensagem*" :sucesso="validacao.mensagem">
                                <textarea v-model="form.mensagem" class="textarea"
                                    :class="{ 'is-danger': foiTocado && !form.mensagem, 'is-success': validacao.mensagem }"
                                    placeholder="Como podemos ajudar?"></textarea>
                            </CampoFormulario>
                        </div>
                    </div>

                    <div v-if="!formularioValido" class="has-text-right mb-3">
                        <p class="is-size-7 has-text-grey">
                            Aguardando campos:
                            <span :class="form.nome.length < 3 ? 'has-text-danger' : 'has-text-success'">Nome</span> |
                            <span :class="!emailValido ? 'has-text-danger' : 'has-text-success'">E-mail</span> |
                            <span
                                :class="form.empresa.length < 2 ? 'has-text-danger' : 'has-text-success'">Empresa</span>
                            |
                            <span
                                :class="form.mensagem.length < 10 ? 'has-text-danger' : 'has-text-success'">Mensagem</span>
                        </p>
                    </div>

                    <div class="has-text-right">
                        <button type="submit" class="button is-dark is-medium" :class="{ 'is-loading': enviando }"
                            :disabled="!formularioValido || enviando" @mouseenter="foiTocado = true">
                            <span>ENVIAR MENSAGEM</span>
                            <span class="icon"><i class="fas fa-paper-plane"></i></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <AlertaSucesso :exibir="exibirAlerta" @fechar="exibirAlerta = false" />
    </section>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import AlertaSucesso from '../components/AlertaSucesso.vue';
import CampoFormulario from '../components/CampoFormulario.vue';

const form = ref({ nome: '', email: '', telefone: '', empresa: '', tamanho: '', mensagem: '' });
const enviando = ref(false);
const foiTocado = ref(false);
const exibirAlerta = ref(false);

const emailValido = computed(() => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email));

const validacao = computed(() => ({
    nome: form.value.nome.trim().length >= 3,
    email: emailValido.value,
    empresa: form.value.empresa.trim().length >= 2,
    mensagem: form.value.mensagem.trim().length >= 10
}));

const formularioValido = computed(() =>
    Object.values(validacao.value).every(v => v === true)
);

const aplicarMascaraTelefone = (e: Event) => {
    let v = (e.target as HTMLInputElement).value.replace(/\D/g, '');
    v = v.replace(/^(\d{2})(\d)/g, "($1) $2");
    v = v.replace(/(\d)(\d{4})$/, "$1-$2");
    form.value.telefone = v;
};

const submeterFormulario = async () => {
    foiTocado.value = true;
    if (!formularioValido.value) return;
    enviando.value = true;
    await new Promise(resolve => setTimeout(resolve, 1500));
    enviando.value = false;
    exibirAlerta.value = true;
    form.value = { nome: '', email: '', telefone: '', empresa: '', tamanho: '', mensagem: '' };
    foiTocado.value = false;
};
</script>

<style scoped>
.contact-box {
    padding: 3rem;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.input,
.textarea,
.select select {
    transition: all 0.3s ease;
}
</style>