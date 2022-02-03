<template>
    <div
        class="relative flex flex-col min-w-0 break-words w-full mb-6 shadow-lg rounded"
        :class="[color === 'light' ? 'bg-white' : 'bg-emerald-900 text-white']"
    >
        <div class="rounded-t mb-0 px-4 py-3 border-0">
            <div class="flex flex-wrap items-center">
                <div class="relative w-full px-4 max-w-full flex-grow flex-1">
                    <h3
                        class="font-semibold text-lg"
                        :class="[color === 'light' ? 'text-blueGray-700' : 'text-white']"
                    >
                        Generate unique key for website
                    </h3>
                </div>
            </div>
        </div>
        <div class="block w-full overflow-x-auto p-4">
            <!-- Projects table -->
            <div class="relative w-full mb-3">
                <label
                    class="block uppercase text-blueGray-600 text-xs font-bold mb-2"
                    htmlFor="grid-password"
                >
                    Website :
                </label>
                <input
                    type="email"
                    class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600 bg-white rounded text-sm shadow focus:outline-none focus:ring w-full ease-linear transition-all duration-150"
                    placeholder="Name of website"
                    v-model="name"
                />
            </div>

            <div class="text-center mt-6">
                <button
                    @click="doCreate()"
                    class="bg-blueGray-800 text-white active:bg-blueGray-600 text-sm font-bold  px-6 py-3 rounded shadow hover:shadow-lg outline-none focus:outline-none mr-1 mb-1 w-full ease-linear transition-all duration-150"
                    type="button"
                    :disabled="isLoading"
                >
                    <span v-if="isLoading" class="fas fa-spinner fa-spin">
                    </span>
                    <span v-else>
                        Create
                    </span>

                </button>
            </div>



        </div>
    </div>
</template>

<script>
export default {
    name: "CardCreateWebsite",
    props: {
        color: {
            default: "light",
            validator: function (value) {
                // The value must match one of these strings
                return ["light", "dark"].indexOf(value) !== -1;
            },
        },
    },
    data(){
        return {
            name: "",
            isLoading: false
        }
    },
    methods:{
        doCreate(){
            this.isLoading = true
            let data = {
                name: this.name
            }
            axios.post("/api/create-website", data)
                    .then(res => {
                        this.name = ""
                    })
                    .catch(err => {
                        console.log(err)
                    })
                    .finally(() =>{
                        this.isLoading =  false

                        this.emitter.emit("reload_navbar", true);
                    })
        }
    }
}
</script>

<style scoped>

</style>
