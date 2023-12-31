import React, {useState} from "react";
import Authenticated from "@/Layouts/Authenticated";
import { Link, Head, usePage } from "@inertiajs/inertia-react";
import PageTitle from "@/Components/PageTitle";
import Pagination from "@/Components/Common/Pagination";

export default function Index(props) {
    const [permissions, setPermissions] = useState(props.auth.permissions);
    const isShowCompany = permissions.some(permission => {
        if (permission.name === 'show company') {
            return true;
        }
        return false;
    });
    // 👇️ check if array contains object
    const isCreateCustomer = permissions.some(permission => {
        if (permission.name === 'create customer') {
            return true;
        }
        return false;
    });
    // 👇️ check if array contains object
    const isEditCustomer = permissions.some(permission => {
        if (permission.name === 'edit customer') {
            return true;
        }
        return false;
    });
    const { customers, companies, shops } = usePage().props;
    return (
        <>
            <Authenticated auth={props.auth} errors={props.errors}>
                <Head title="Customers" />
                <PageTitle>Customers</PageTitle>
                {isCreateCustomer &&
                <Link  href={route('customers.create')} className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 border border-blue-700 rounded w-24 mb-5 text-base"> 
                    <i  className="fas fa-plus"></i> Add 
                </Link>
                }
                <div className="mb-4 w-full rounded-lg bg-white p-4 shadow-md dark:bg-gray-800">
                    <div className="mb-2 grid grid-cols-5 gap-2">
                        <div className="flex flex-col">
                            <label for="customer_phone" className="mb-2 font-semibold">Customer Mobile</label>
                            <input type="text" id="customer_phone" className="block w-full mt-1 text-sm border rounded border-gray-300 dark:border-gray-600 
                                    dark:bg-gray-700 focus:border-purple-400 focus:outline-none 
                                    focus:shadow-outline-purple dark:text-gray-300 
                                    dark:focus:shadow-outline-gray form-input" />
                        </div>

                        <div className="flex flex-col">
                            <label for="customer_email" className="mb-2 font-semibold">Customer Email</label>
                            <input type="email" id="customer_email" className="block w-full mt-1 text-sm border rounded border-gray-300 dark:border-gray-600 
                                    dark:bg-gray-700 focus:border-purple-400 focus:outline-none 
                                    focus:shadow-outline-purple dark:text-gray-300 
                                    dark:focus:shadow-outline-gray form-input" />
                        </div>
                        {isShowCompany &&
                        <div className="flex flex-col">
                            <label for="order_status" className="mb-2 font-semibold">Company</label>
                            <select name="order_status" id="order_status" className="block w-full mt-1 border rounded border-gray-300 dark:border-gray-600 
                                dark:bg-gray-700 focus:border-purple-400 focus:outline-none 
                                focus:shadow-outline-purple dark:text-gray-300 
                                dark:focus:shadow-outline-gray form-input">
                                    <option value="">Select Company</option> 
                                    {companies && companies.map((value, index) => (
                                        <option value={value.id}>{value.name}</option>
                                    ))}                        

                            </select>
                        </div>
                        }
                        
                        <div className="flex flex-col">
                            <label for="order_status" className="mb-2 font-semibold">Shops</label>
                            <select name="order_status" id="order_status" className="block w-full mt-1 text-sm border rounded border-gray-300 dark:border-gray-600 
                                dark:bg-gray-700 focus:border-purple-400 focus:outline-none 
                                focus:shadow-outline-purple dark:text-gray-300 
                                dark:focus:shadow-outline-gray form-input">
                                    <option value="">Select Shop</option> 
                                    {shops && shops.map((value, index) => (
                                        <option value={value.id}>{value.title}</option>
                                    ))}                        

                            </select>
                        </div>
                        <div className="flex flex-col">
                            <label for="order_date" className="mb-6 font-semibold"></label>
                            <button
                                type="button"
                                class="border border-indigo-500 bg-indigo-500 text-white rounded-md px-4 py-2 m-2 transition duration-500 ease select-none hover:bg-indigo-600 focus:outline-none focus:shadow-outline"
                            >
                                <i class="fa-solid fa-magnifying-glass"></i> Search
                            </button>
                        </div>                   
                        
                    </div>
                </div>
                <PageTitle>Customer List</PageTitle>
                <div className="mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">
                    <div className="w-full overflow-hidden rounded-lg shadow-xs mb-8">
                        <div className="w-full overflow-x-auto">
                            <table className="w-full whitespace-no-wrap">
                                <thead className="text-lg font-semibold tracking-wide text-left text-gray-500 capitalize border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                                    <tr>
                                        <th className="px-4 py-3">Id</th>
                                        <th className="px-4 py-3">Name</th>
                                        <th className="px-4 py-3">Company</th>
                                        <th className="px-4 py-3">Shop</th>
                                        <th className="px-4 py-3">Email</th>
                                        <th className="px-4 py-3">Mobile</th>
                                        {isEditCustomer &&
                                            <th className="px-4 py-3">Action</th> 
                                        }
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800 text-gray-700 dark:text-gray-400">
                                    {customers.data.map((value, index) => (
                                        <tr key={index}>
                                            <td className="px-4 py-3">
                                                {value.id}
                                            </td>
                                            <td className="px-4 py-3">
                                                {value.name}
                                            </td>
                                            <td className="px-4 py-3">
                                                {value.company? value.company.name:''}
                                            </td>
                                            <td className="px-4 py-3">
                                                {value.shop? value.shop.title: ''}
                                            </td>
                                            <td className="px-4 py-3">
                                                {value.email}
                                            </td>
                                            <td className="px-4 py-3">
                                                {value.phone}
                                            </td>
                                            {isEditCustomer &&
                                            <td className="px-4 py-3">
                                                <Link  href={route('customers.edit',value.id )} className="btnInfo hover:btnInfo text-white py-2 mr-2 px-4 shadow-md rounded"> 
                                                    <i className="fas fa-pencil"></i>
                                                </Link>
                                            </td>
                                            }
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                        <Pagination links={customers.links} />
                    </div>
                </div>
            </Authenticated>
        </>
    );
}
