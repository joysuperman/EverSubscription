import { Link } from "react-router-dom";

export default function Navbar() {
    return (
        <nav className="bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between">
            <ul className="text-lg font-semibold text-gray-900">
                <li className="hover:text-blue-600">
                    <Link to="/">Dashboard</Link>
                    <Link to="/settings" className="ml-6">Settings</Link>
                </li>
            </ul>
        </nav>
    );
}