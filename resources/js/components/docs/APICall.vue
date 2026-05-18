<template>
	<div
		class="flex flex-col"
		:class="{'mb-2' : isExpanded}"
	>

		<!-- HEADER -->
		<div
			class="flex flex-row justify-between bg-gray-300 p-2 px-3 cursor-pointer rounded items-center transition-all transform duration-300"
			:class="{
				'rounded-b-none bg-gray-400' : isExpanded
			}"
			@click="toggleIsExpanded"
		>
			<h2 class="flex flex-row items-center gap-2 font-semibold text-md tracking-wide" :id="name">
				<span
					class="px-1 rounded text-white text-sm"
					:class="primaryColorBg"
				>{{method}} </span>
				<span>{{ name }}</span>
			</h2>
			<HeroiconChevronDown v-if="isExpanded" customClass="w-5 h-5 pointer-events-none" />
			<HeroiconChevronLeft v-else customClass="w-5 h-5 pointer-events-none" />
		</div>

        <div class="hidden">
            <span class="text-blue-600 bg-blue-600">-</span>
            <span class="text-pink-500 bg-pink-500">-</span>
            <span class="text-yellow-600 bg-yellow-600">-</span>
            <span class="text-red-600 bg-red-600">-</span>
            <span class="text-green-600 bg-green-600">-</span>
        </div>


		<!-- BODY -->
		<div
			v-if="isExpanded"
			class="flex flex-col gap-3 p-4 pt-2 border border-gray-400 border-t-0 rounded rounded-t-none"
		>

			<h3>{{ description || `-` }}</h3>

			<div class="flex flex-row gap-3">
				<div class="flex flex-row">

					<!-- METHOD -->
					<div class="p-2 px-3 border border-gray-500 font-bold rounded-l"
						:class="primaryColorText"
					>{{ method }}</div>

					<!-- URL -->
					<div class="p-2 px-3 border border-l-0 border-gray-500 rounded-r">
						<div class="flex flex-row">
							<span
								v-for="(segment, index) of splitUrl"
								:key="index"
							>
								<span
									:class="getPathParameterColorText(segment)"
								>
								{{segment}}</span><span v-if="index < splitUrl.length - 1">/</span>
							</span>
						</div>
					</div>
				</div>

			</div>

			<div class="flex flex-col gap-3">

				<div v-if="Object.keys(pathParameters).length > 0">
					<table class="table-auto text-left">
						<thead class="border border-gray-500 border-separate">
							<tr class="border border-gray-500 border-separate">
								<th class="p-1 px-2 border border-gray-500 border-separate">Parámetro de ruta</th>
								<th class="p-1 px-2 border border-gray-500 border-separate">Descripción</th>
								<th class="p-1 px-2 border border-gray-500 border-separate" v-if="isTestingExpanded">
									<span class="w-full">Valor de prueba</span>
								</th>
							</tr>
						</thead>
						<tbody class="">
							<tr v-for="(param, index) of pathParameters" :key="index" class="border border-gray-500 border-separate">
								<td :class="`p-1 px-2 border border-gray-500 border-separate`">
                                    <span :class="getPathParameterColorText(`{${param.name}}`)">{{`\{${param.name}\}`}}</span>
                                    </td>
								<td class="p-1 px-2 border border-gray-500 border-separate">{{ param.description || `-` }}</td>
								<td class="p-1 px-2 border border-gray-500 border-separate" v-if="isTestingExpanded">
									<InputText type="text" v-model="param.value" class="w-full api-section" placeholder="Value..." />
								</td>
							</tr>

						</tbody>
					</table>
				</div>

				<div v-if="Object.keys(params).length > 0">
					<table class="table-auto text-left">
						<thead class="border border-gray-500 border-separate">
							<tr class="border border-gray-500 border-separate">
								<th class="p-1 px-2 border border-gray-500 border-separate">Parámetro</th>
								<th class="p-1 px-2 border border-gray-500 border-separate">Descripción</th>
								<th class="p-1 px-2 border border-gray-500 border-separate" v-if="isTestingExpanded">
									<span class="w-full">Valor de prueba</span>
								</th>
							</tr>
						</thead>
						<tbody class="">
							<tr v-for="(param, index) of params" :key="index" class="border border-gray-500 border-separate">
								<td class="p-1 px-2 border border-gray-500 border-separate">{{ param.name }}</td>
								<td class="p-1 px-2 border border-gray-500 border-separate">{{ param?.description || `-` }}</td>
								<td class="p-1 border border-gray-500 border-separate" v-if="isTestingExpanded">
									<InputText type="text" v-model="param.value" class="w-full api-section" placeholder="Value..." />
								</td>
							</tr>

						</tbody>
					</table>
				</div>
			</div>

            <div v-if="isTestingExpanded && name != 'login'">
                <table class="table-auto text-left">
                    <thead class="border border-gray-500 border-separate">
                        <tr class="border border-gray-500 border-separate">
                            <th class="p-1 px-2 border border-gray-500 border-separate">Bearer token</th>
                            <td class="p-1 px-2 border border-gray-500 border-separate">
                                <InputText type="text" v-model="bearerToken" class="w-full api-section" placeholder="Token..." />
                            </td>
                        </tr>
                    </thead>
                </table>
            </div>

			<div class="flex flex-row gap-3">

				<ButtonAction
					:color="isTestingExpanded ? 'gray' : 'primary'"
					@action="showExample"
				>
					<div class="flex flex-row justify-center gap-1 items-center">
						<span>{{ isTestingExpanded ? 'Cancelar' : 'Probar llamada' }}</span>
						<HeroiconCog8Tooth v-if="!isTestingExpanded" customClass="w-5 h-5 pointer-events-none" />
					</div>
				</ButtonAction>

				<ButtonAction v-if="isTestingExpanded" class="w-auto" @action="executeCall">
					<div class="flex flex-row justify-center gap-1 items-center">
						<span class="pl-1">Ejecutar</span>
						<HeroiconPlay customClass="w-5 h-5 pointer-events-none" />
					</div>
				</ButtonAction>
			</div>

			<div v-if="result && isTestingExpanded" class="w-full max-w-screen-lg flex flex-col gap-2 overflow-hidden rounded text-xs leading-4">

				<highlightjs
                    language="json"
                    style="white-space: pre-wrap; overflow-wrap: break:word"
                    class="w-full"
                    :autodetect="false"
                    :code="arrayToLiteralString(result?.data)"
                >
					{{ result }}
				</highlightjs>
			</div>

            <!-- <Debug t="result" :c="JSON.stringify(result?.data)"/> -->

			<slot />
		</div>

	</div>
</template>

<script>

// SYNTAX HIGHLIGHT
// import Vue from 'vue'
// import VueHighlightJS from 'vue-highlight.js';
// import 'vue-highlight.js/lib/allLanguages'
import './vue-highlight-styles.css';

import axios from 'axios';

import hljs from 'highlight.js/lib/common';
import hljsVuePlugin from "@highlightjs/vue-plugin";

// Vue.use(hljsVuePlugin);

export default{
	name: "APICall",
	mixins: [],
	components: {
        highlightjs: hljsVuePlugin.component,
	},

	props: {

		name: {
			type: String,
			required: true
		},
		description: {
			type: String,
			required: true
		},
		section: {
			type: String,
			required: true,
		},
		pathParameters: {
			type: [Array, Object],
			default: () => {}
		},
		params: {
			type: [Array, Object],
			default: () => {}
		},
		method: {
			type: String,
			required: true
		},
		url: {
			type: String,
			required: true
		},
		jsonCall: {
			type: [Array, Object],
			default: () => {},
		},
	},

	data() {
		return {
			isExpanded: false,
			isTestingExpanded: false,
			result: undefined,
			acceptedMethods: ['POST', 'GET', 'PUT', 'DELETE'],
			splitUrl: [],
			count: 0,
			pathParametersColors: [
                'blue-600',
				'pink-500',
				'yellow-600',
				'red-600',
				'green-600',
			],
			localSection: '',
			sanitizedUrl: '',
			originalUrl: '',
            primaryColorBg: '',
            primaryColorText: '',
            bearerToken: null,
		};
	},



	computed: {
		methodClassBg() {
			if (this.method) {
				switch (this.method) {
					case 'POST':
						return 'bg-green-600';
					case 'GET':
						return 'bg-purple-500';
					case 'PUT':
						return 'bg-orange-500';
					case 'DELETE':
						return 'bg-red-600';
					case 'PATCH':
						return 'bg-yellow-600';

					default:
						return 'bg-black';
				}
			}
		},

		methodClassText() {
			if (this.method) {
				switch (this.method) {
					case 'POST':
						return 'text-green-600';
					case 'GET':
						return 'text-purple-500';
					case 'PUT':
						return 'text-orange-500';
					case 'DELETE':
						return 'text-red-600';
					case 'PATCH':
						return 'text-yellow-600';

					default:
						return 'text-black';
				}
			}
		},
	},

	mounted() {
        this.primaryColorBg = this.methodClassBg;
        this.primaryColorText = this.methodClassText;

		this.localSection = this.section.toLowerCase().replace(' ', '_');
		this.sanitizedUrl = this.sanitizeUrl(this.url);
		this.splitUrl = this.sanitizedUrl.split('/');

		// Clear parameters values
		for (const param in this.params) {
			if (Object.hasOwnProperty.call(this.params, param)) {
				const element = this.params[param];
				element.value = null;
			}
		}

		// Clear path parameters values
		for (const param in this.pathParameters) {
			if (Object.hasOwnProperty.call(this.pathParameters, param)) {
				const element = this.pathParameters[param];
				element.value = null;
			}
		}

	},

	methods: {
        arrayToLiteralString(arr) {
            return JSON.stringify(arr, null, 2)
        },

		showExample() {
			this.isTestingExpanded = !this.isTestingExpanded;
			this.result = null;
		},

		toggleIsExpanded() {
			if (this.isTestingExpanded) {
				this.isTestingExpanded = false;
				this.result = null;
			}
			this.isExpanded = !this.isExpanded;
		},

		async executeCall() {
			if (this.acceptedMethods.find(f => f == this.method) && this.splitUrl) {

				// Convert the JSON object into a proper params object
				const params = this.params.reduce((acc, obj) => {
					acc[obj.name] = obj.value;
					return acc;
				}, {});

				let newSplitUrl = [...this.splitUrl];

				newSplitUrl.forEach((segment, index) => {
					if (/^{([^{}]+)}$/.test(segment)) {
						const value = this.pathParameters.find(f => `\{${f.name}\}` == segment).value;
						newSplitUrl[index] = value;
					}
				});

				newSplitUrl = [...newSplitUrl];

				const newUrl = newSplitUrl.join('/');

				// const result = await this.$axios[this.method.toLowerCase()](
				// 	`https://paneletno.devecomputer.com/${newUrl}`,
				// 	{
				// 		params: params
				// 	},
				// 	{
				// 		headers: {},
				// 		withCredentials: false
				// 	}
				// );

				const testAPIAxios = new axios.create({
					withCredentials: false,
					baseURL: "https://ekd.devecomputer.com",
					// baseURL: "http://localhost:8000",
				});


                // API Authentication
                // Get token
                // const response = await testAPIAxios.post('/api/login', {email: "root@ecomputer.es", password: "1234"});
                // const token = response?.data?.token;

                if (this.bearerToken) {
                    // Add a request interceptor
                    testAPIAxios.interceptors.request.use(
                        (config) => {
                            config.headers.Authorization = `Bearer ${this.bearerToken}`;

                            return config;
                        },
                        (error) => {
                            return Promise.reject(error);
                        }
                    );
                }

                // Perform the call
				let result = {};

				switch (this.method) {
                    case 'DELETE':
                    case 'PATCH':
					case 'GET':
                        try {
                            result = await testAPIAxios[this.method.toLowerCase()](newUrl, {params: params});
                        } catch (error) {
                            result = {data: {data: error.error || error.errors || error.message || "An unexpected error occurred"}};
                        }
						break;
					case 'POST':
					case 'DELETE':
                    case 'PUT':
                        try {
                            result = {data: await testAPIAxios[this.method.toLowerCase()](newUrl, params)};
                        } catch (error) {
                            result = {data: {data: error.error || error.errors || error.message || "An unexpected error occurred" }};
                        }
						break;

					default:
                        break;
				}

				this.result = {...result.data};

			}
		},

		getPathParameterColorBg(string) {

			if (/^{([^{}]+)}$/.test(string)) {
				const count = this.splitUrl.filter(segment => /^{([^{}]+)}$/.test(segment)).indexOf(string);
				if (count !== -1) {
				return `bg-${this.pathParametersColors[count]}`;
				}
			}

			return '';
		},

		getPathParameterColorText(string) {

			if (/^{([^{}]+)}$/.test(string)) {
				const count = this.splitUrl.filter(segment => /^{([^{}]+)}$/.test(segment)).indexOf(string);
				if (count !== -1) {
				return `text-${this.pathParametersColors[count]}`;
				}
			}

			return '';
		},

		sanitizeUrl(url) {
			let newUrl = url.replace('{{host}}/', '');
			newUrl = newUrl.replace(new RegExp('^(.*?)(\\?.*)?$'), '$1');
			newUrl = newUrl.trim();
			return newUrl;
		}

	}
}
</script>

<style>
	.api-section .form-input {
		padding-left: 0.5rem;
		padding-right: 0.5rem;
	}
</style>
