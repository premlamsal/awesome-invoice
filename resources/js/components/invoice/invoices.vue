<template>
  <div>
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Invoices</h1>
    <p class="mb-4" v-if="hasPermission('add_invoice')">
      <router-link class="btn btn-primary" to="/newInvoice">New Invoice</router-link>
    </p>

       


              <!-- <label class="switch">
  <input type="checkbox" checked>
  <span class="slider round"></span>
</label> -->


    <!-- DataTales Example -->
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary" style="display: inline-block;">Invoices</h6>

        <div v-if="isLoading">{{isLoading}}

        </div>
        <div v-if="isLoading">{{isLoading}}</div>
        <!-- {{isLoading}} -->
        <div class="searchTable">
          <!-- Topbar Search -->
          <!-- <div class="input-group"> -->
          <div class="input-group no-border">
            <input type="text" value class="form-control" placeholder="Search..." v-model="searchTableKey" />
            <div class="input-group-append">
              <div class="input-group-text">
                <i class="nc-icon nc-zoom-split" @click="searchTableBtn"></i>
              </div>
            </div>
          </div>
          <!-- </div> -->
        </div>
      </div>
      <div class="card-body" v-if="invoices.length > 0">



        <div class="table">
          <table class="table table-striped table-bordered" width="100%" cellspacing="0">
            <thead>
              <tr>
                <th>Invoice No.</th>
                <th>Grand Total</th>
                <th>Client</th>
                <th>Invoice Date</th>
                <th>Due Date</th>
                <th>Status</th>
                <!-- <th>Last Modified at</th> -->
                <th>Modify</th>
              </tr>
            </thead>
            <!--  <tfoot>
                    <tr>
                      <th>Invoice No.</th>
                      <th>Grand Total</th>
                      <th>Client</th>
                      <th>Invoice Date</th>
                      <th>Due Date</th>
                      <th>Created At</th>
                    </tr>
            </tfoot>-->
            <tbody>


              <tr v-for="invoice in invoices" v-bind:key="invoice.id">
                <td>{{invoice.custom_invoice_id}}</td>
                <td>Rs. {{invoice.grand_total}}</td>
                <td>{{invoice.customer_name}}</td>
                <td>{{invoice.invoice_date | moment("from","now")}}</td>
                <td>
                  <span v-if="(invoice.due_date<=todayDate)" class="bg-danger text-white p-2">{{invoice.due_date | moment("from","now")}}</span> 
                  <span v-else class="bg-success text-white p-2">{{invoice.due_date | moment("from","now")}}</span>
                </td>
                <td>{{invoice.invoice_date | moment("from", "now")}}</td>
                <td>
                  <span v-if="(invoice.invoice_date === invoice.due_date)" class="bg-danger text-white p-2">{{invoice.due_date | moment("from", "now")}}</span>
                  <span v-else class="bg-success text-white p-2">{{invoice.due_date | moment("from", "now")}}</span>
                </td>
<!-- tempStatus[invoice.id] = $event -->

                <td v-if="(invoice.status==='Paid')">
                       <toggle-button v-bind:status="true" @statusChanges ="updateStatus($event,invoice.id)"/> 
                </td>
                
                <td v-else-if="(invoice.status==='To Pay')">
                       <toggle-button v-bind:status="false" @statusChanges ="updateStatus($event,invoice.id)"/> 
                </td>

                <!-- <td>{{invoice.updated_at | moment("from", "now")}}</td> -->
                <td>
                  <button class="btn btn-outline-primary custom_btn_table" @click="showInvoice(invoice.id)" v-if="hasPermission('show_invoice')">
                    <span class="fa fa-align-justify custom_icon_table"></span>
                  </button>
                  <button class="btn btn-outline-success custom_btn_table" @click="editInvoice(invoice.id)" v-if="hasPermission('edit_invoice')">
                    <span class="fa fa-edit custom_icon_table"></span>
                  </button>
                  <!-- <button class="btn btn-outline-success" @click="returnInvoice(invoice.id)" style="margin-right: 5px;"><span class="fa fa-reply" style="font-size: 15px"></span>
                  </button>-->
                  <button class="btn btn-outline-danger custom_btn_table" @click="deleteInvoice(invoice.id)" v-if="hasPermission('delete_invoice')">
                    <span class="fa fa-trash custom_icon_table"></span>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="row">
          <div class="col-md-8">
            <ul class="pagination">
              <li class="page-item" v-bind:class="{disabled:!pagination.first_link}">
                <button @click="fetchInvoices(pagination.first_link)" class="page-link">First</button>
              </li>
              <li class="page-item" v-bind:class="{disabled:!pagination.prev_link}">
                <button @click="fetchInvoices(pagination.prev_link)" class="page-link">Previous</button>
              </li>
              <li v-for="n in pagination.last_page" v-bind:key="n" class="page-item" v-bind:class="{active:pagination.current_page == n}">
                <button @click="fetchInvoices(pagination.path_page + n)" class="page-link">{{n}}</button>
              </li>
              <li class="page-item" v-bind:class="{disabled:!pagination.next_link}">
                <button @click="fetchInvoices(pagination.next_link)" class="page-link">Next</button>
              </li>
              <li class="page-item" v-bind:class="{disabled:!pagination.last_link}">
                <button @click="fetchInvoices(pagination.last_link)" class="page-link">Last</button>
              </li>
            </ul>
          </div>
          <div class="col-md-4">
            Page: {{pagination.current_page}}-{{pagination.last_page}} Total Records: {{pagination.total_pages}}
          </div>
        </div>
      </div>
      <div class="errorDivEmptyData" v-else>No Data Found</div>

    </div>
  </div>
</template>

<script>
//custom toggle button
import ToggleButton from "../widgets/ToggleButton";

export default {
  components:{
      ToggleButton,
  },
  data() {
    return {
      invoices: [],
      invoice_id: "",
      pagination: {},
      edit: false,
      searchTableKey: "",
      isLoading: "",
      tempStatus:{},
      todayDate:""
    };
  },

  created() {
    this.fetchInvoices();

  },

  methods: {
  
  },

  methods: {
    fetchInvoices(page_url) {
      this.$Progress.start();
      this.isLoading = "Loading Data";
      page_url = page_url || "/api/invoices";
      let vm = this;
      axios
        .get(page_url)
        .then(function(response) {
          vm.invoices = response.data.data;
          vm.isLoading = "";
          if (vm.invoices.length != null) {
            vm.makePagination(response.data.meta, response.data.links);
            vm.$Progress.finish();
            vm.isLoading = "";
          }
        })
        .catch(function(error) {
          vm.$Progress.fail();
        });
    },
    makePagination(meta, links) {
      let pagination = {
        current_page: meta.current_page,
        last_page: meta.last_page,
        from_page: meta.from,
        to_page: meta.to,
        total_pages: meta.total,
        path_page: meta.path + "?page=",
        first_link: links.first,
        last_link: links.last,
        prev_link: links.prev,
        next_link: links.next
      };
      this.pagination = pagination;
    },

    deleteInvoice(id) {
      this.$Progress.start();
      let currObj = this;
      this.$swal({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!"
      }).then(result => {
        if (result.value) {

          //delete code here
          axios
            .delete("/api/invoice/" + id)

          .then(function(response) {
              // alert('Purchase Removed');
              currObj.output = response.data.msg;
              currObj.status = response.data.status;

              //will get index of that invoice to delete and delete only that particular invoice only, to reduce server load to refresh everytime when data changed on database from this particular frontend view ----for ex, this.fetchInvoices();
              let index_to_delete_invoice = currObj.invoices.findIndex(invoice => invoice.id === id)
              //splice will delete that invoice from the array as specfied with index
              currObj.invoices.splice(index_to_delete_invoice,1);

              currObj.$Progress.finish();
              // alert(currObj.status);
              currObj.$swal("Info", currObj.output, currObj.status);
            })
            .catch(function(response) {
              currObj.$Progress.fail();
            });
        }
      });
    },
    editInvoice(id) {
      this.$Progress.start();
      // named route
      // this.$router.push({ name: 'editInvoice', params: { id } });
      this.$router.push({ path: `${id}/editInvoice/` });

      this.$Progress.finish();
    },
    returnInvoice(id) {
      this.$Progress.start();
      // named route
      // this.$router.push({ name: 'editInvoice', params: { id } });
      this.$router.push({ path: `${id}/returnInvoice/` });
      this.$Progress.finish();
    },
    showInvoice(id) {
      this.$Progress.start();
      // named route

      // this.$router.push({ name: 'showInvoice', params: { id } }); //will hide parameter in url

      this.$router.push({ path: `${id}/showInvoice/` });

      this.$Progress.finish();
    },
    searchTableBtn() {
      this.autoCompleteTable();
    },
    updateStatus(event,key){
      this.tempStatus[key]=event;

      let formData=new FormData();
      formData.append("_method","PUT");
      formData.append("key",key);
      if(event==true){
        formData.append("value","Paid");  
      }
      else{
        formData.append("value","To Pay");
      }
      
      axios.post('/api/changeInvoiceStatus',formData)
      .then(response=>{

         this.$toast.success({
          title: response.data.status,
          message: response.data.msg
        });



      })
      .catch(error=>{

        this.$toast.error({
          title: "Error",
          message: "some error occured"
        });

        this.fetchInvoices();


      });
    },

    autoCompleteTable() {
      this.searchTableKey = this.searchTableKey.toLowerCase();
      if (this.searchTableKey != "") {
        this.isLoading = "Loading Data...";
        let currObj = this;
        axios
          .post("/api/invoices/search", { searchQuery: this.searchTableKey })
          .then(function(response) {
            currObj.isLoading = "";

            currObj.invoices = response.data.data;
            if (response.data.data == "") {
              currObj.isLoading = "No Data Found";
            }
            // if((this.estimates.length)!=null){
            // // currObj.makePagination(res.meta,res.links);
            // }
            // currObj.status=response.data.status;
            currObj.errors = ""; //clearing errors
          })
          .catch(function(error) {
            if (error.response.status == "422") {
              currObj.validationErrors = error.response.data.errors;
              currObj.errors = currObj.validationErrors;
              currObj.isLoading = "Load Failed...";
              // console.log(currObj.errors);
            }
          });
      } else {
        this.isLoading = "Loading all Data";
        this.fetchInvoices();
      }
    }, //end of autoCOmpleteTable
    hasPermission(action) {
      let permissions_from_store = this.$store.getters.permissions;

      if (
        permissions_from_store.includes(action) ||
        permissions_from_store.includes("all")
      ) {
        return true;
      } else {
        return false;
      }
    } //has permision
  } //end of methods
}; //end of default
</script>
<style>
  .switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}

</style>
