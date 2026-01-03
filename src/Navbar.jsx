import { Link } from "react-router-dom";

export default function Navbar() {
    return (
        <nav className="py-3 flex items-center justify-between mb-6">
            <ul className="text-sm font-semibold text-gray-900 flex space-x-4">
                <li className="hover:text-blue-600">
                    <Link to="/">Dashboard</Link>
                </li>
                <li className="hover:text-blue-600">
                    <Link to="/settings">Settings</Link>
                </li>
            </ul>
        </nav>
    );
}