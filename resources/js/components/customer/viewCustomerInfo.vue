<template>
  <div>
     <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Customer Information</h1>
    <div class="row">

      <div class="col-md-6">
        
  <div class="card shadow mb-4">
     <!--  <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary" style="display: inline-block;"> 
          {{customer.name}}
        </h6>       
      </div> -->
       <div class="card-body">
        <div class="customer-info-panel">
          <h1 class="text-primary"><i class="nc-icon nc-single-02"></i></h1>
        <h3 class="text-primary">{{customer.name}}</h3>
          <span style="display: block;">{{customer.address}}</span>
          <span style="display: block;">{{customer.phone}}</span>
          <span style="display: block;">{{customer.details}}</span>
        </div>
       </div>
    </div>


      </div>
      <div class="col-md-6">
          <div class="card shadow mb-4">
     <!--  <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary" style="display: inline-block;"> 
        Extra Details
        </h6>       
      </div> -->
       <div class="card-body">
        <div class="customer-info-panel">
          <h4 class="mt-1">Total Invoices </h4>
          <h4>Total Business Amount</h4>
          <h4>Total Due Amount</h4>
        </div>
       </div>
    </div>
      </div>
   
    

  
  </div>
  <div class="row">
    <div class="col-md-12">
      



      
    </div>
  </div>
  </div>
</template>
<style scoped="">
  .customer-info-panel{
    text-align: center;
    margin-top: 1em;
  }
  .customer-info-panel h1,.customer-info-panel h3,.customer-info-panel h4{
    margin: 0px;
  }

</style>
<script>
export default{

  data(){
    return {

      customer_id:'',
      customer:{},

    };
  },

  created(){
    this.getIdFromUrl();
    this.getCustomer();
  },

  methods:{
    getIdFromUrl() {
      this.customer_id = this.$route.params.id;
    }, //end of getIdFromUrl

    getCustomer(){
      axios.get('/api/customer/'+this.customer_id)
      .then(response=>{
        console.log(response.data.customer);
        this.customer=response.data.customer;
      })
      .catch(error=>{
        console.log(error);
      })
    }
  }
}
</script>
